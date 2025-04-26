import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/providers/auth-provider';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: 'admin' | 'sales' | 'executive' | string[];
}

export default function ProtectedRoute({ 
  children, 
  requiredRole 
}: ProtectedRouteProps) {
  const { user, loading, isAdmin, isSales, isExecutive } = useAuth();
  const location = useLocation();
  
  if (loading) {
    // Show loading state while checking authentication
    return (
      <div className="h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }
  
  // Not authenticated, redirect to login
  if (!user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }
  
  // Check if a specific role is required
  if (requiredRole) {
    let hasRequiredRole = false;
    
    if (typeof requiredRole === 'string') {
      // Single role check
      if (
        (requiredRole === 'admin' && isAdmin) ||
        (requiredRole === 'sales' && isSales) ||
        (requiredRole === 'executive' && isExecutive)
      ) {
        hasRequiredRole = true;
      }
    } else if (Array.isArray(requiredRole)) {
      // Check for any of the roles in the array
      if (
        (requiredRole.includes('admin') && isAdmin) ||
        (requiredRole.includes('sales') && isSales) ||
        (requiredRole.includes('executive') && isExecutive)
      ) {
        hasRequiredRole = true;
      }
    }
    
    if (!hasRequiredRole) {
      // User doesn't have the required role
      return <Navigate to="/admin" replace />;
    }
  }
  
  // User is authenticated and has the required role
  return <>{children}</>;
}