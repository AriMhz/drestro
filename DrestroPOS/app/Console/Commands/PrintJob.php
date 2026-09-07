<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\PrinterService;
use Illuminate\Support\Facades\Log;

#[Signature('app:print-job {file : The path to the serialized print job JSON file}')]
#[Description('Execute a serialized ESC/POS thermal printing job in the background.')]
class PrintJob extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("Print job file not found: {$filePath}");
            return 1;
        }

        try {
            $content = file_get_contents($filePath);
            $payload = json_decode($content, true);

            if (!$payload) {
                $this->error("Invalid print job payload.");
                @unlink($filePath);
                return 1;
            }

            $jobType = $payload['job_type'] ?? '';

            if ($jobType === 'receipt') {
                $orderId = $payload['order_id'];
                $force = $payload['force'] ?? false;

                $order = \App\Models\Order::with(['items.menuItem', 'user', 'table', 'invoice'])->find($orderId);
                if ($order) {
                    PrinterService::printReceipt($order, $force);
                } else {
                    $this->error("Order #{$orderId} not found for receipt printing.");
                }
            } elseif ($jobType === 'hotel_bill') {
                // Reconstruct Table (room) model from snapshot
                $roomData = $payload['room'];
                $room = new \App\Models\Table();
                $room->name = $roomData['name'] ?? 'Room';
                $room->guest_name = $roomData['guest_name'] ?? null;
                $room->room_rate = $roomData['room_rate'] ?? null;
                $room->check_in_at = $roomData['check_in_at'] ?? null;

                // Reconstruct Orders and OrderItems collection
                $orders = collect();
                foreach ($payload['orders'] as $orderData) {
                    $order = new \App\Models\Order();
                    $order->order_number = $orderData['order_number'] ?? '';
                    $order->created_at = isset($orderData['created_at']) ? \Carbon\Carbon::parse($orderData['created_at']) : now();
                    $order->total_amount = $orderData['total_amount'] ?? 0;

                    $items = collect();
                    foreach ($orderData['items'] as $itemData) {
                        $item = new \App\Models\OrderItem();
                        $item->quantity = $itemData['quantity'] ?? 0;
                        $item->subtotal = $itemData['subtotal'] ?? 0;

                        $menuItem = new \App\Models\MenuItem();
                        $menuItem->name = $itemData['menu_item']['name'] ?? 'Item';

                        $item->setRelation('menuItem', $menuItem);
                        $items->push($item);
                    }
                    $order->setRelation('items', $items);
                    $orders->push($order);
                }

                PrinterService::printHotelBill($room, $orders);
            } else {
                $this->error("Unsupported job type: {$jobType}");
            }

            $this->info("Print job completed successfully.");
        } catch (\Exception $e) {
            Log::error("Background Print Job Failed: " . $e->getMessage());
            $this->error("Print job failed: " . $e->getMessage());
        } finally {
            // Always clean up the temporary file
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return 0;
    }
}
