import axios from 'axios';
import Cookies from 'js-cookie';

// Base URL of the API - use environment variable or fallback
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000/api';

// Create axios instance with common configuration
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add authorization header if token exists
api.interceptors.request.use((config) => {
  const token = Cookies.get('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add error handling interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle network errors gracefully
    if (!error.response) {
      console.error('Network Error:', error.message);
      return Promise.reject(new Error('Network error. Please check your connection and try again.'));
    }
    return Promise.reject(error);
  }
);

// Auth related API calls
export const AuthAPI = {
  register: async (email: string, password: string, firstName: string, lastName: string) => {
    const response = await api.post('/auth/register.php', {
      email,
      password,
      first_name: firstName,
      last_name: lastName,
    });
    
    if (response.data.token) {
      Cookies.set('token', response.data.token, { expires: 1 }); // expires in 1 day
    }
    
    return response.data;
  },
  
  login: async (email: string, password: string) => {
    const response = await api.post('/auth/login.php', {
      email,
      password,
    });
    
    if (response.data.token) {
      Cookies.set('token', response.data.token, { expires: 1 }); // expires in 1 day
    }
    
    return response.data;
  },
  
  logout: () => {
    Cookies.remove('token');
  },
  
  getCurrentUser: () => {
    const token = Cookies.get('token');
    if (token) {
      try {
        // Parse the JWT token
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
          atob(base64)
            .split('')
            .map(function (c) {
              return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join('')
        );
        
        const { data, exp } = JSON.parse(jsonPayload);
        
        // Check if token is expired
        if (exp * 1000 < Date.now()) {
          Cookies.remove('token');
          return null;
        }
        
        return data;
      } catch (error) {
        Cookies.remove('token');
        return null;
      }
    }
    return null;
  },
};

// User related API calls
export const UserAPI = {
  getUsers: async () => {
    const response = await api.get('/users/read.php');
    return response.data;
  },
  
  getUser: async (id: string) => {
    const response = await api.get(`/users/read_one.php?id=${id}`);
    return response.data;
  },
  
  updateUser: async (id: string, firstName: string, lastName: string, role: string) => {
    const response = await api.put('/users/update.php', {
      id,
      first_name: firstName,
      last_name: lastName,
      role,
    });
    return response.data;
  },
  
  deleteUser: async (id: string) => {
    const response = await api.delete(`/users/delete.php?id=${id}`);
    return response.data;
  },
};

// Category related API calls
export const CategoryAPI = {
  getCategories: async () => {
    const response = await api.get('/categories/read.php');
    return response.data;
  },
  
  getCategory: async (id: string) => {
    const response = await api.get(`/categories/read_one.php?id=${id}`);
    return response.data;
  },
  
  getCategoryBySlug: async (slug: string) => {
    const response = await api.get(`/categories/read_by_slug.php?slug=${slug}`);
    return response.data;
  },
  
  createCategory: async (name: string, slug: string, description?: string, imageUrl?: string) => {
    const response = await api.post('/categories/create.php', {
      name,
      slug,
      description,
      image_url: imageUrl,
    });
    return response.data;
  },
  
  updateCategory: async (id: string, name: string, slug: string, description?: string, imageUrl?: string) => {
    const response = await api.put('/categories/update.php', {
      id,
      name,
      slug,
      description,
      image_url: imageUrl,
    });
    return response.data;
  },
  
  deleteCategory: async (id: string) => {
    const response = await api.delete(`/categories/delete.php?id=${id}`);
    return response.data;
  },
};

// Product related API calls
export const ProductAPI = {
  getProducts: async (categoryId?: string) => {
    const url = categoryId 
      ? `/products/read.php?category_id=${categoryId}`
      : '/products/read.php';
    const response = await api.get(url);
    return response.data;
  },
  
  getProduct: async (id: string) => {
    const response = await api.get(`/products/read_one.php?id=${id}`);
    return response.data;
  },
  
  getProductBySlug: async (slug: string) => {
    const response = await api.get(`/products/read_by_slug.php?slug=${slug}`);
    return response.data;
  },
  
  createProduct: async (productData: any) => {
    const response = await api.post('/products/create.php', productData);
    return response.data;
  },
  
  updateProduct: async (productData: any) => {
    const response = await api.put('/products/update.php', productData);
    return response.data;
  },
  
  deleteProduct: async (id: string) => {
    const response = await api.delete(`/products/delete.php?id=${id}`);
    return response.data;
  },
  
  searchProducts: async (keywords: string) => {
    const response = await api.get(`/products/search.php?keywords=${keywords}`);
    return response.data;
  },
};

// Quote related API calls
export const QuoteAPI = {
  getQuotes: async () => {
    const response = await api.get('/quotes/read.php');
    return response.data;
  },
  
  getQuotesByStatus: async (status: string) => {
    const response = await api.get(`/quotes/read_by_status.php?status=${status}`);
    return response.data;
  },
  
  getQuote: async (id: string) => {
    const response = await api.get(`/quotes/read_one.php?id=${id}`);
    return response.data;
  },
  
  createQuote: async (customerName: string, customerEmail: string, message: string, customerPhone?: string, companyName?: string, productId?: string) => {
    const response = await api.post('/quotes/create.php', {
      customer_name: customerName,
      customer_email: customerEmail,
      customer_phone: customerPhone,
      company_name: companyName,
      product_id: productId,
      message,
    });
    return response.data;
  },
  
  updateQuote: async (id: string, status: string, assignedTo?: string) => {
    const response = await api.put('/quotes/update.php', {
      id,
      status,
      assigned_to: assignedTo,
    });
    return response.data;
  },
  
  getQuoteStats: async () => {
    const response = await api.get('/quotes/get_stats.php');
    return response.data;
  },
};

// Notification related API calls
export const NotificationAPI = {
  getNotifications: async (userId: string) => {
    const response = await api.get(`/notifications/read_by_user.php?user_id=${userId}`);
    return response.data;
  },
  
  getUnreadNotifications: async (userId: string) => {
    const response = await api.get(`/notifications/read_unread_by_user.php?user_id=${userId}`);
    return response.data;
  },
  
  markAsRead: async (id: string, userId: string) => {
    const response = await api.put('/notifications/mark_as_read.php', {
      id,
      user_id: userId,
    });
    return response.data;
  },
  
  markAllAsRead: async (userId: string) => {
    const response = await api.put('/notifications/mark_all_as_read.php', {
      user_id: userId,
    });
    return response.data;
  },
  
  countUnread: async (userId: string) => {
    const response = await api.get(`/notifications/count_unread.php?user_id=${userId}`);
    return response.data;
  },
};

// Company settings related API calls
export const CompanySettingsAPI = {
  getSettings: async () => {
    const response = await api.get('/company_settings/read.php');
    return response.data;
  },
  
  updateSettings: async (settingsData: any) => {
    const response = await api.put('/company_settings/update.php', settingsData);
    return response.data;
  },
};

export default api;