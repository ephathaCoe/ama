import { Outlet, Link, useNavigate, useLocation } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { 
  ChevronDown, 
  Menu, 
  X, 
  LayoutDashboard, 
  ListChecks, 
  Package, 
  MessageSquare, 
  Users, 
  Settings, 
  Bell, 
  LogOut, 
  User
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuth } from '@/providers/auth-provider';
import { NotificationAPI } from '@/lib/api';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';

export default function AdminLayout() {
  const { user, signOut, isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  
  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
  };
  
  useEffect(() => {
    const fetchUnreadNotifications = async () => {
      try {
        if (user) {
          const response = await NotificationAPI.countUnread(user.id);
          setUnreadCount(response.count);
        }
      } catch (error) {
        console.error('Error fetching unread notifications:', error);
      }
    };
    
    if (user) {
      fetchUnreadNotifications();
      
      // Refresh unread count every minute
      const interval = setInterval(fetchUnreadNotifications, 60000);
      return () => clearInterval(interval);
    }
  }, [user]);
  
  const handleLogout = () => {
    signOut();
    navigate('/login');
    toast.success('You have been signed out successfully');
  };
  
  const markAllAsRead = async () => {
    try {
      if (user) {
        await NotificationAPI.markAllAsRead(user.id);
        setUnreadCount(0);
        toast.success('All notifications marked as read');
      }
    } catch (error) {
      console.error('Error marking notifications as read:', error);
      toast.error('Failed to mark notifications as read');
    }
  };
  
  const navItems = [
    {
      title: 'Dashboard',
      path: '/admin',
      icon: <LayoutDashboard className="h-5 w-5" />,
      roles: ['admin', 'sales', 'executive'],
    },
    {
      title: 'Categories',
      path: '/admin/categories',
      icon: <ListChecks className="h-5 w-5" />,
      roles: ['admin', 'sales'],
    },
    {
      title: 'Products',
      path: '/admin/products',
      icon: <Package className="h-5 w-5" />,
      roles: ['admin', 'sales'],
    },
    {
      title: 'Quotes',
      path: '/admin/quotes',
      icon: <MessageSquare className="h-5 w-5" />,
      roles: ['admin', 'sales', 'executive'],
    },
    {
      title: 'Users',
      path: '/admin/users',
      icon: <Users className="h-5 w-5" />,
      roles: ['admin'],
    },
    {
      title: 'Settings',
      path: '/admin/settings',
      icon: <Settings className="h-5 w-5" />,
      roles: ['admin', 'executive'],
    },
  ];
  
  // Filter navigation items based on user role
  const filteredNavItems = navItems.filter(item => {
    if (!user || !user.role) return false;
    return item.roles.includes(user.role);
  });
  
  return (
    <div className="min-h-screen flex">
      {/* Sidebar */}
      <aside className={`fixed inset-y-0 left-0 z-50 w-64 bg-primary-foreground border-r transform ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'} md:translate-x-0 transition-transform duration-200 ease-in-out`}>
        <div className="h-full flex flex-col">
          {/* Sidebar Header */}
          <div className="h-16 flex items-center justify-between px-4 border-b md:justify-center">
            <Link to="/admin" className="text-xl font-bold">
              Amaris Admin
            </Link>
            <button
              onClick={toggleSidebar}
              className="md:hidden p-2 rounded-md hover:bg-muted"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
          
          {/* Navigation */}
          <nav className="flex-1 overflow-y-auto p-2">
            <ul className="space-y-1">
              {filteredNavItems.map((item) => (
                <li key={item.path}>
                  <Link
                    to={item.path}
                    className={`flex items-center gap-3 rounded-md px-3 py-2 hover:bg-muted transition-colors ${location.pathname === item.path ? 'bg-muted font-medium' : ''}`}
                    onClick={() => setIsSidebarOpen(false)}
                  >
                    {item.icon}
                    <span>{item.title}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
          
          {/* User Menu */}
          <div className="p-4 border-t">
            {user && (
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <div className="h-9 w-9 rounded-full bg-primary text-primary-foreground flex items-center justify-center mr-2">
                    <span className="font-medium text-sm">
                      {user.first_name.charAt(0)}{user.last_name.charAt(0)}
                    </span>
                  </div>
                  <div>
                    <p className="font-medium">{user.first_name} {user.last_name}</p>
                    <p className="text-xs text-muted-foreground capitalize">{user.role}</p>
                  </div>
                </div>
                <button
                  onClick={handleLogout}
                  className="p-2 rounded-md hover:bg-muted"
                  title="Logout"
                >
                  <LogOut className="h-5 w-5" />
                </button>
              </div>
            )}
          </div>
        </div>
      </aside>
      
      {/* Main Content */}
      <div className="flex-1 md:ml-64">
        {/* Top Bar */}
        <header className="h-16 border-b bg-background flex items-center justify-between px-4">
          <div className="flex items-center">
            <button
              onClick={toggleSidebar}
              className="md:hidden p-2 rounded-md hover:bg-muted mr-2"
            >
              <Menu className="h-6 w-6" />
            </button>
            <h1 className="text-xl font-bold">{getPageTitle(location.pathname)}</h1>
          </div>
          
          <div className="flex items-center gap-2">
            {/* Notifications */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" size="icon" className="relative">
                  <Bell className="h-5 w-5" />
                  {unreadCount > 0 && (
                    <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0">
                      {unreadCount > 9 ? '9+' : unreadCount}
                    </Badge>
                  )}
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-80">
                <div className="flex items-center justify-between p-2">
                  <h4 className="font-medium">Notifications</h4>
                  <Button variant="ghost" size="sm" onClick={markAllAsRead}>
                    Mark all read
                  </Button>
                </div>
                <DropdownMenuSeparator />
                <div className="py-6 text-center text-sm text-muted-foreground">
                  {unreadCount === 0 ? (
                    "You're all caught up!"
                  ) : (
                    "Go to notifications page to view all"
                  )}
                </div>
              </DropdownMenuContent>
            </DropdownMenu>
            
            {/* User Menu */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center">
                    <span className="font-medium text-sm">
                      {user?.first_name.charAt(0)}{user?.last_name.charAt(0)}
                    </span>
                  </div>
                  <span className="hidden md:inline">{user?.first_name} {user?.last_name}</span>
                  <ChevronDown className="h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem className="cursor-pointer" onClick={() => navigate('/admin/profile')}>
                  <User className="mr-2 h-4 w-4" />
                  <span>Profile</span>
                </DropdownMenuItem>
                <DropdownMenuItem className="cursor-pointer" onClick={() => navigate('/admin/settings')}>
                  <Settings className="mr-2 h-4 w-4" />
                  <span>Settings</span>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem className="cursor-pointer" onClick={handleLogout}>
                  <LogOut className="mr-2 h-4 w-4" />
                  <span>Logout</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </header>
        
        {/* Page Content */}
        <main className="p-4">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

// Helper function to get page title from path
function getPageTitle(path: string): string {
  if (path === '/admin') return 'Dashboard';
  
  const parts = path.split('/');
  if (parts.length > 2) {
    const pageName = parts[2];
    return pageName.charAt(0).toUpperCase() + pageName.slice(1);
  }
  
  return 'Admin';
}