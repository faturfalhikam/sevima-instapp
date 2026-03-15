<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import TopNavigation from '@/Components/TopNavigation.vue';
import BottomNavigation from '@/Components/BottomNavigation.vue';
import PostCard from '@/Components/PostCard.vue';
import CommentModal from '@/Components/CommentModal.vue';

const page = usePage();
const posts = ref([]);
const currentPage = ref(1);
const isLoading = ref(false);
const hasMorePosts = ref(true);
const observerElement = ref(null);

// Comment modal state
const activeCommentPost = ref(null);
const showCommentModal = ref(false);

// Current authenticated user
const currentUser = computed(() => {
    const user = page.props.auth?.user;
    if (!user) return null;
    return {
        ...user,
        profile_photo_url: user.profile_photo_path
            ? `/storage/${user.profile_photo_path}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`,
    };
});

// Helper to get CSRF token from meta tag or Inertia props
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) return token;
    return page.props.csrf_token || '';
};

// Helper to read XSRF-TOKEN cookie (set by Sanctum for SPA)
const getXsrfToken = () => {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
};

console.log('Auth user:', page.props.auth?.user);
console.log('CSRF Token available:', !!getCsrfToken());

// Fetch feed posts
const fetchFeed = async (pageNum = 1) => {
    if (isLoading.value || !hasMorePosts.value) return;

    isLoading.value = true;
    try {
        const url = new URL('/api/feed', window.location.origin);
        url.searchParams.append('page', pageNum);

        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'include',
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            console.error(`HTTP error! status: ${response.status}`, errorData);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        const postsData = data?.data || [];
        const paginationData = data?.pagination || {};

        if (pageNum === 1) {
            posts.value = Array.isArray(postsData) ? postsData : [];
        } else {
            posts.value = [...(Array.isArray(posts.value) ? posts.value : []), ...(Array.isArray(postsData) ? postsData : [])];
        }

        currentPage.value = paginationData.current_page || pageNum;
        hasMorePosts.value = paginationData.has_more || false;
    } catch (error) {
        console.error('Error fetching feed:', error);
        if (!Array.isArray(posts.value)) {
            posts.value = [];
        }
    } finally {
        isLoading.value = false;
    }
};

// Handle like
const handleLike = async (postId, currentLikeStatus) => {
    try {
        const response = await fetch(`/api/posts/${postId}/like`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'include',
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (Array.isArray(posts.value)) {
            const post = posts.value.find(p => p.id === postId);
            if (post) {
                post.is_liked = data.liked;
                post.likes_count = data.likes_count;
            }
        }
    } catch (error) {
        console.error('Error liking post:', error);
    }
};

// Open comment modal for a post
const openComments = (post) => {
    activeCommentPost.value = post;
    showCommentModal.value = true;
};

// Close comment modal
const closeComments = () => {
    showCommentModal.value = false;
    // Small delay before clearing the post so the close animation can finish
    setTimeout(() => { activeCommentPost.value = null; }, 350);
};

// Update comments count after a comment is added
const handleCommentAdded = (postId, newCount) => {
    if (Array.isArray(posts.value)) {
        const post = posts.value.find(p => p.id === postId);
        if (post) {
            post.comments_count = newCount;
        }
    }
};

// Intersection Observer for infinite scroll
const setupIntersectionObserver = () => {
    const options = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting && hasMorePosts.value && !isLoading.value) {
                fetchFeed(currentPage.value + 1);
            }
        });
    }, options);

    if (observerElement.value) {
        observer.observe(observerElement.value);
    }

    return observer;
};

onMounted(() => {
    fetchFeed(1);
    setupIntersectionObserver();
});
</script>

<template>
    <Head title="Home" />

    <div class="bg-background-light dark:bg-background-dark min-h-screen">
        <!-- Top Navigation -->
        <TopNavigation />

        <!-- Main Feed -->
        <main class="max-w-md mx-auto pb-20">
            <div v-if="Array.isArray(posts) && posts.length === 0 && !isLoading" class="text-center py-10">
                <p class="text-slate-500 dark:text-slate-400">No posts yet. Start following users to see their posts!</p>
            </div>

            <!-- Posts -->
            <TransitionGroup v-if="Array.isArray(posts)" name="post-list" tag="div">
                <PostCard
                    v-for="post in posts"
                    :key="post.id"
                    :post="post"
                    @like="handleLike"
                    @open-comments="openComments"
                />
            </TransitionGroup>

            <!-- Infinite scroll trigger -->
            <div ref="observerElement" class="py-10 text-center">
                <div v-if="isLoading" class="flex justify-center">
                    <div class="inline-flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce"></div>
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
                <p v-else-if="!hasMorePosts" class="text-slate-500 dark:text-slate-400 text-sm">
                    No more posts to load
                </p>
            </div>
        </main>

        <!-- Bottom Navigation -->
        <BottomNavigation />
    </div>

    <!-- Comment Modal (teleported to body to avoid stacking context issues) -->
    <Teleport to="body">
        <CommentModal
            :post="activeCommentPost"
            :show="showCommentModal"
            :current-user="currentUser"
            @close="closeComments"
            @comment-added="handleCommentAdded"
        />
    </Teleport>
</template>

<style scoped>
.post-list-move,
.post-list-enter-active,
.post-list-leave-active {
    transition: all 0.5s ease;
}

.post-list-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.post-list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

.post-list-leave-active {
    position: absolute;
}
</style>
