export const useImage = () => {
    const resolve = (path, type = 'default') => {
        if (!path) return getPlaceholder(type);

        // If it's a full URL, return as is
        if (path.startsWith('http')) return path;

        // Handle relative backend paths like ../img/pf10.jpeg
        // We assume backend might serve these from /storage or public
        // But given the example ../img, it often needs cleaning.

        // Remove leading dots or slashes for cleaner appending
        const cleanPath = path.replace(/^(\.\.\/|\.\/|\/)+/, '');

        // If we assume a Laravel backend serving from public/storage
        // Adjust this base URL if needed (e.g. import.meta.env.VITE_API_URL)
        // For now assuming the images are serving from root relative or assets

        // If the path was like "img/pf10.jpeg", we return it as absolute
        return `/${cleanPath}`;
    };

    const getPlaceholder = (type) => {
        switch (type) {
            case 'user':
                return 'https://ui-avatars.com/api/?name=User&background=random';
            case 'game':
                return 'https://placehold.co/600x400?text=No+Image'; // Better placeholder service
            default:
                return 'https://placehold.co/400x400?text=No+Image';
        }
    };

    return { resolve, getPlaceholder };
};
