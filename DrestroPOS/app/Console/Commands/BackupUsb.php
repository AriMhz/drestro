<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Helpers\BackupHelper;

#[Signature('app:backup-usb')]
#[Description('Scan connected USB drives and backup database asynchronously.')]
class BackupUsb extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting USB database backup...');
        $drives = BackupHelper::backupToUsb();
        if (count($drives) > 0) {
            $this->info('USB backup completed successfully to drives: ' . implode(', ', $drives));
        } else {
            $this->info('No USB drives detected or backup skipped.');
        }
    }
}
