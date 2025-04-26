import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';

import './index.css';
import { Toaster } from '@/components/ui/sonner';
import { ThemeProvider } from '@/components/theme-provider';
import CartProvider from '@/providers/cart-provider';

// Public routes
import PublicLayout from '@/layouts/public-layout';
import HomePage from '@/pages/home';
import AboutPage from '@/pages/about';
import ProductsPage from '@/pages/products';
import ProductDetailPage from '@/pages/product-detail';
import RequestQuotePage from '@/pages/request-quote';
import ContactPage from '@/pages/contact';

// Admin routes
import AdminLayout from '@/layouts/admin-layout';
import LoginPage from '@/pages/auth/login';
import RegisterPage from '@/pages/auth/register';
import DashboardPage from '@/pages/admin/dashboard';
import CategoriesPage from '@/pages/admin/categories';
import ProductsAdminPage from '@/pages/admin/products';
import QuotesPage from '@/pages/admin/quotes';
import UsersPage from '@/pages/admin/users';
import SettingsPage from '@/pages/admin/settings';
import AuthProvider from '@/providers/auth-provider';
import ProtectedRoute from '@/components/protected-route';

const router = createBrowserRouter([
  {
    path: '/',
    element: <PublicLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'about', element: <AboutPage /> },
      { path: 'products', element: <ProductsPage /> },
      { path: 'products/:productSlug', element: <ProductDetailPage /> },
      { path: 'request-quote', element: <RequestQuotePage /> },
      { path: 'contact', element: <ContactPage /> },
    ],
  },
  {
    path: '/admin',
    element: (
      <ProtectedRoute>
        <AdminLayout />
      </ProtectedRoute>
    ),
    children: [
      { index: true, element: <DashboardPage /> },
      { path: 'categories', element: <CategoriesPage /> },
      { path: 'products', element: <ProductsAdminPage /> },
      { path: 'quotes', element: <QuotesPage /> },
      { path: 'users', element: <UsersPage /> },
      { path: 'settings', element: <SettingsPage /> },
    ],
  },
  { path: '/login', element: <LoginPage /> },
  { path: '/register', element: <RegisterPage /> },
]);

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ThemeProvider defaultTheme="light" storageKey="amaris-theme">
      <AuthProvider>
        <CartProvider>
          <RouterProvider router={router} />
          <Toaster />
        </CartProvider>
      </AuthProvider>
    </ThemeProvider>
  </StrictMode>
);