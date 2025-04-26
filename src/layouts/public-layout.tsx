import { Outlet } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Menu, X, ChevronDown, Phone, Mail, MapPin } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { CompanySettingsAPI } from '@/lib/api';
import { ThemeToggle } from '@/components/theme-toggle';
import CartSheet from '@/components/cart-sheet';

interface CompanySettings {
  name: string;
  logo_url: string | null;
  contact_email: string;
  contact_phone: string | null;
  address: string | null;
}

export default function PublicLayout() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [settings, setSettings] = useState<CompanySettings | null>(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const response = await CompanySettingsAPI.getSettings();
        setSettings(response);
      } catch (error) {
        console.error('Error fetching company settings:', error);
      } finally {
        setLoading(false);
      }
    };
    
    fetchSettings();
  }, []);
  
  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };
  
  return (
    <div className="min-h-screen flex flex-col">
      {/* Top Bar */}
      <div className="bg-primary text-primary-foreground py-2 px-4">
        <div className="container mx-auto flex justify-between items-center">
          <div className="flex items-center space-x-4 text-sm">
            {settings?.contact_phone && (
              <div className="flex items-center">
                <Phone className="h-4 w-4 mr-1" />
                <span>{settings.contact_phone}</span>
              </div>
            )}
            {settings?.contact_email && (
              <div className="flex items-center">
                <Mail className="h-4 w-4 mr-1" />
                <span>{settings.contact_email}</span>
              </div>
            )}
            {settings?.address && (
              <div className="flex items-center">
                <MapPin className="h-4 w-4 mr-1" />
                <span className="hidden md:inline">{settings.address}</span>
              </div>
            )}
          </div>
          <div className="flex items-center gap-2">
            <ThemeToggle />
            <CartSheet />
          </div>
        </div>
      </div>
      
      {/* Main Navigation */}
      <header className="bg-background border-b">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center h-20">
            {/* Logo */}
            <Link to="/" className="flex items-center space-x-2">
              {settings?.logo_url ? (
                <img 
                  src={settings.logo_url} 
                  alt={settings.name} 
                  className="h-10"
                />
              ) : (
                <div className="font-bold text-2xl">{loading ? 'Loading...' : settings?.name || 'Amaris'}</div>
              )}
            </Link>
            
            {/* Desktop Navigation */}
            <nav className="hidden md:flex space-x-8">
              <Link to="/" className="font-medium hover:text-primary transition-colors">
                Home
              </Link>
              <Link to="/about" className="font-medium hover:text-primary transition-colors">
                About
              </Link>
              <div className="relative group">
                <Link 
                  to="/products" 
                  className="flex items-center font-medium hover:text-primary transition-colors"
                >
                  Products
                  <ChevronDown className="ml-1 h-4 w-4" />
                </Link>
                <div className="absolute left-0 z-10 mt-2 w-48 origin-top-left rounded-md bg-background shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden group-hover:block">
                  <div className="py-1">
                    <Link to="/products" className="block px-4 py-2 hover:bg-muted">
                      All Products
                    </Link>
                    {/* Categories will be dynamically populated here */}
                  </div>
                </div>
              </div>
              <Link to="/request-quote" className="font-medium hover:text-primary transition-colors">
                Request Quote
              </Link>
              <Link to="/contact" className="font-medium hover:text-primary transition-colors">
                Contact
              </Link>
            </nav>
            
            {/* Mobile Menu Button */}
            <div className="md:hidden">
              <button
                onClick={toggleMenu}
                className="p-2 rounded-md hover:bg-muted"
                aria-expanded={isMenuOpen}
                aria-label="Toggle menu"
              >
                {isMenuOpen ? (
                  <X className="h-6 w-6" />
                ) : (
                  <Menu className="h-6 w-6" />
                )}
              </button>
            </div>
            
            {/* Call to Action */}
            <div className="hidden md:block">
              <Button asChild>
                <Link to="/request-quote">Request Quote</Link>
              </Button>
            </div>
          </div>
          
          {/* Mobile Navigation */}
          {isMenuOpen && (
            <div className="md:hidden py-4 space-y-2 pb-4">
              <Link 
                to="/" 
                className="block py-2 px-4 rounded hover:bg-muted"
                onClick={() => setIsMenuOpen(false)}
              >
                Home
              </Link>
              <Link 
                to="/about" 
                className="block py-2 px-4 rounded hover:bg-muted"
                onClick={() => setIsMenuOpen(false)}
              >
                About
              </Link>
              <Link 
                to="/products" 
                className="block py-2 px-4 rounded hover:bg-muted"
                onClick={() => setIsMenuOpen(false)}
              >
                Products
              </Link>
              <Link 
                to="/request-quote" 
                className="block py-2 px-4 rounded hover:bg-muted"
                onClick={() => setIsMenuOpen(false)}
              >
                Request Quote
              </Link>
              <Link 
                to="/contact" 
                className="block py-2 px-4 rounded hover:bg-muted"
                onClick={() => setIsMenuOpen(false)}
              >
                Contact
              </Link>
            </div>
          )}
        </div>
      </header>
      
      {/* Main Content */}
      <main className="flex-1">
        <Outlet />
      </main>
      
      {/* Footer */}
      <footer className="bg-primary text-primary-foreground py-12">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
              <h3 className="text-lg font-bold mb-4">About Us</h3>
              <p className="text-sm opacity-80">
                Amaris Heavy Machinery is a leading provider of high-quality machinery for building, mining, and manufacturing industries.
              </p>
            </div>
            <div>
              <h3 className="text-lg font-bold mb-4">Quick Links</h3>
              <ul className="space-y-2">
                <li><Link to="/" className="text-sm opacity-80 hover:opacity-100">Home</Link></li>
                <li><Link to="/about" className="text-sm opacity-80 hover:opacity-100">About</Link></li>
                <li><Link to="/products" className="text-sm opacity-80 hover:opacity-100">Products</Link></li>
                <li><Link to="/request-quote" className="text-sm opacity-80 hover:opacity-100">Request Quote</Link></li>
                <li><Link to="/contact" className="text-sm opacity-80 hover:opacity-100">Contact</Link></li>
              </ul>
            </div>
            <div>
              <h3 className="text-lg font-bold mb-4">Contact</h3>
              <ul className="space-y-2">
                {settings?.address && (
                  <li className="flex text-sm opacity-80">
                    <MapPin className="h-5 w-5 mr-2 shrink-0" />
                    <span>{settings.address}</span>
                  </li>
                )}
                {settings?.contact_phone && (
                  <li className="flex text-sm opacity-80">
                    <Phone className="h-5 w-5 mr-2 shrink-0" />
                    <span>{settings.contact_phone}</span>
                  </li>
                )}
                {settings?.contact_email && (
                  <li className="flex text-sm opacity-80">
                    <Mail className="h-5 w-5 mr-2 shrink-0" />
                    <span>{settings.contact_email}</span>
                  </li>
                )}
              </ul>
            </div>
            <div>
              <h3 className="text-lg font-bold mb-4">Newsletter</h3>
              <p className="text-sm opacity-80 mb-4">
                Subscribe to our newsletter for the latest updates.
              </p>
              <div className="flex flex-col space-y-2">
                <input 
                  type="email" 
                  placeholder="Your email"
                  className="px-4 py-2 rounded text-foreground bg-background"
                />
                <Button>Subscribe</Button>
              </div>
            </div>
          </div>
          <div className="mt-8 pt-8 border-t border-primary-foreground/20 text-center">
            <p className="text-sm opacity-70">
              &copy; {new Date().getFullYear()} Amaris Heavy Machinery. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
}