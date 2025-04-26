import { ShoppingCart, Minus, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useCart } from '@/providers/cart-provider';
import { useNavigate } from 'react-router-dom';
import { Badge } from '@/components/ui/badge';

export default function CartSheet() {
  const { items, removeItem, updateQuantity, totalItems, clearCart } = useCart();
  const navigate = useNavigate();

  const handleQuoteRequest = () => {
    navigate('/request-quote');
  };

  if (totalItems === 0) {
    return (
      <Sheet>
        <SheetTrigger asChild>
          <Button variant="outline" size="icon">
            <ShoppingCart className="h-5 w-5" />
          </Button>
        </SheetTrigger>
        <SheetContent>
          <SheetHeader>
            <SheetTitle>Quote Cart</SheetTitle>
          </SheetHeader>
          <div className="flex flex-col items-center justify-center h-[calc(100vh-200px)]">
            <p className="text-muted-foreground">Your quote cart is empty</p>
            <Button
              variant="outline"
              className="mt-4"
              onClick={() => navigate('/products')}
            >
              Browse Products
            </Button>
          </div>
        </SheetContent>
      </Sheet>
    );
  }

  return (
    <Sheet>
      <SheetTrigger asChild>
        <Button variant="outline" size="icon" className="relative">
          <ShoppingCart className="h-5 w-5" />
          <Badge
            className="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center p-0"
            variant="secondary"
          >
            {totalItems}
          </Badge>
        </Button>
      </SheetTrigger>
      <SheetContent>
        <SheetHeader>
          <SheetTitle>Quote Cart ({totalItems} items)</SheetTitle>
        </SheetHeader>
        <div className="mt-8">
          <div className="space-y-4">
            {items.map((item) => (
              <div
                key={item.id}
                className="flex items-center justify-between py-4 border-b"
              >
                <div>
                  <h3 className="font-medium">{item.name}</h3>
                  {item.price && (
                    <p className="text-sm text-muted-foreground">
                      ${item.price}
                    </p>
                  )}
                </div>
                <div className="flex items-center gap-2">
                  <div className="flex items-center gap-1">
                    <Button
                      variant="outline"
                      size="icon"
                      className="h-8 w-8"
                      onClick={() => updateQuantity(item.id, item.quantity - 1)}
                    >
                      <Minus className="h-4 w-4" />
                    </Button>
                    <span className="w-8 text-center">{item.quantity}</span>
                    <Button
                      variant="outline"
                      size="icon"
                      className="h-8 w-8"
                      onClick={() => updateQuantity(item.id, item.quantity + 1)}
                    >
                      <Plus className="h-4 w-4" />
                    </Button>
                  </div>
                  <Button
                    variant="destructive"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => removeItem(item.id)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-8 space-y-4">
            <Button
              className="w-full"
              onClick={handleQuoteRequest}
            >
              Request Quote
            </Button>
            <Button
              variant="outline"
              className="w-full"
              onClick={clearCart}
            >
              Clear Cart
            </Button>
          </div>
        </div>
      </SheetContent>
    </Sheet>
  );
}