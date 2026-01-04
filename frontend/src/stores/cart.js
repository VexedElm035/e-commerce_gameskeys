import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const count = ref(0);
    const isLoading = ref(false);
    const error = ref(null);
    const clearingCart = ref(false);

    const fetchCart = async () => {
        try {
            isLoading.value = true;
            error.value = null;
            const response = await axios.get('/cart');
            
            if (Array.isArray(response.data)) {
                items.value = response.data;
                count.value = items.value.length;
            } else {
                items.value = [];
                count.value = 0;
                console.error('La respuesta del carrito no es un array:', response.data);
            }
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar el carrito';
            console.error('Error fetching cart:', err);
        } finally {
            isLoading.value = false;
        }
    };

    const addToCart = async (keyId) => {
        try {
            isLoading.value = true;
            error.value = null;
            await axios.post('/cart', { key_id: keyId });
            await fetchCart();
            window.dispatchEvent(new CustomEvent('cart-updated'));
            return true;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al añadir al carrito';
            console.error('Error adding to cart:', err);
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    const removeFromCart = async (cartItemId) => {
        try {
            await axios.delete(`/cart/${cartItemId}`);
            await fetchCart();
            window.dispatchEvent(new CustomEvent('cart-updated'));
        } catch (err) {
            console.error('Error removing from cart:', err);
            throw err;
        }
    };

    const clearCart = async () => {
        try {
            clearingCart.value = true;
            error.value = null;
            
            await fetchCart();
            
            const deletePromises = items.value.map(item => 
                axios.delete(`/cart/${item.id}`)
            );
            
            await Promise.all(deletePromises);
            
            items.value = [];
            count.value = 0;
            
            window.dispatchEvent(new CustomEvent('cart-updated'));
            
            return true;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al vaciar el carrito';
            console.error('Error clearing cart:', err);
            throw err;
        } finally {
            clearingCart.value = false;
        }
    };

    return {
        items,
        count,
        isLoading,
        error,
        clearingCart,
        fetchCart,
        addToCart,
        removeFromCart,
        clearCart
    };
});