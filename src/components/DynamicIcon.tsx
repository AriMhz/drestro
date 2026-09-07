import { 
  Package, 
  Monitor, 
  Printer, 
  Wifi, 
  CheckCircle2, 
  Settings, 
  Shield, 
  Smartphone,
  Server,
  Zap,
  CreditCard,
  Cloud,
  LucideProps
} from 'lucide-react';

interface DynamicIconProps extends LucideProps {
  name: string;
}

export function DynamicIcon({ name, ...props }: DynamicIconProps) {
  switch (name.toLowerCase()) {
    case 'monitor':
      return <Monitor {...props} />;
    case 'printer':
      return <Printer {...props} />;
    case 'package':
      return <Package {...props} />;
    case 'wifi':
      return <Wifi {...props} />;
    case 'settings':
      return <Settings {...props} />;
    case 'shield':
      return <Shield {...props} />;
    case 'smartphone':
      return <Smartphone {...props} />;
    case 'server':
      return <Server {...props} />;
    case 'zap':
      return <Zap {...props} />;
    case 'card':
      return <CreditCard {...props} />;
    case 'cloud':
      return <Cloud {...props} />;
    case 'check':
    default:
      return <CheckCircle2 {...props} />;
  }
}
