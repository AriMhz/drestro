import { Metadata } from "next";
import ClientLogosClient from "./ClientLogosClient";

export const metadata: Metadata = {
  title: "Client Logos | Admin | DRestro",
  description: "Manage client logos for the homepage marquee",
};

export default function ClientLogosPage() {
  return <ClientLogosClient />;
}
