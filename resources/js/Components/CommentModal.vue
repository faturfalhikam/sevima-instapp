<script setup>
import { ref, watch, computed, nextTick } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        default: null,
    },
    show: {
        type: Boolean,
        default: false,
    },
    currentUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'comment-added']);

const comments = ref([]);
const postInfo = ref(null);
const isLoading = ref(false);
const commentText = ref('');
const replyingTo = ref(null); // { commentId, username }
const commentInputRef = ref(null);
const isSending = ref(false);

const EMOJIS = ['❤️', '🙌', '🔥', '👏', '😢', '😍', '😮', '😂'];

// Helper to read XSRF-TOKEN cookie
const getXsrfToken = () => {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
};
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

const authHeaders = () => ({
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': getCsrfToken(),
    'X-XSRF-TOKEN': getXsrfToken(),
});

// Fetch comments when the modal opens
watch(() => props.show, async (val) => {
    if (val && props.post) {
        await fetchComments();
    }
});

const fetchComments = async () => {
    if (!props.post) return;
    isLoading.value = true;
    try {
        const res = await fetch(`/api/posts/${props.post.id}/comments`, {
            headers: authHeaders(),
            credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch comments');
        const data = await res.json();
        comments.value = data.comments || [];
        postInfo.value = data.post || null;
    } catch (e) {
        console.error('Error fetching comments:', e);
    } finally {
        isLoading.value = false;
    }
};

const handleSend = async () => {
    if (!commentText.value.trim() || isSending.value) return;
    isSending.value = true;

    try {
        const payload = { content: commentText.value.trim() };
        if (replyingTo.value) {
            payload.parent_id = replyingTo.value.commentId;
        }

        const res = await fetch(`/api/posts/${props.post.id}/comments`, {
            method: 'POST',
            headers: authHeaders(),
            credentials: 'include',
            body: JSON.stringify(payload),
        });

        if (!res.ok) throw new Error('Failed to post comment');
        const data = await res.json();

        if (replyingTo.value) {
            // Add reply to parent comment
            const parent = comments.value.find(c => c.id === replyingTo.value.commentId);
            if (parent) {
                parent.replies = [data.comment, ...(parent.replies || [])];
            }
        } else {
            comments.value.unshift(data.comment);
        }

        emit('comment-added', props.post.id, data.comments_count);
        commentText.value = '';
        replyingTo.value = null;
    } catch (e) {
        console.error('Error posting comment:', e);
    } finally {
        isSending.value = false;
    }
};

const handleLikeComment = async (comment) => {
    try {
        const res = await fetch(`/api/comments/${comment.id}/like`, {
            method: 'POST',
            headers: authHeaders(),
            credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to like comment');
        const data = await res.json();
        comment.is_liked = data.liked;
        comment.likes_count = data.likes_count;
    } catch (e) {
        console.error('Error liking comment:', e);
    }
};

const startReply = async (comment) => {
    replyingTo.value = { commentId: comment.id, username: comment.user.name };
    commentText.value = `@${comment.user.name} `;
    await nextTick();
    commentInputRef.value?.focus();
};

const cancelReply = () => {
    replyingTo.value = null;
    commentText.value = '';
};

const appendEmoji = (emoji) => {
    commentText.value += emoji;
    commentInputRef.value?.focus();
};

const close = () => {
    commentText.value = '';
    replyingTo.value = null;
    emit('close');
};

const userAvatarUrl = computed(() => {
    if (!props.currentUser) return 'https://ui-avatars.com/api/?name=User';
    return props.currentUser.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(props.currentUser.name)}`;
});
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
            @click="close"
        ></div>
    </Transition>

    <!-- Slide-up Modal -->
    <Transition name="slide-up">
        <div
            v-if="show"
            class="fixed inset-x-0 bottom-0 z-50 flex flex-col max-w-md mx-auto bg-white dark:bg-background-dark rounded-t-2xl shadow-2xl"
            style="max-height: 92vh;"
            @click.stop
        >
            <!-- Header -->
            <header class="flex items-center bg-white/90 dark:bg-background-dark/90 backdrop-blur-md px-4 py-3 border-b border-primary/10 rounded-t-2xl shrink-0">
                <button @click="close" class="flex items-center justify-center p-2 rounded-full hover:bg-primary/10 transition-colors">
                    <span class="material-symbols-outlined text-slate-900 dark:text-slate-100">arrow_back</span>
                </button>
                <h1 class="flex-1 text-center text-base font-bold tracking-tight">Comments</h1>
                <div class="w-10"></div>
            </header>

            <!-- Scrollable content -->
            <main class="flex-1 overflow-y-auto pb-2">

                <!-- Post Preview -->
                <div v-if="postInfo || post" class="flex items-start gap-3 p-4 border-b border-primary/5">
                    <div
                        class="h-10 w-10 shrink-0 rounded-full bg-primary/20 bg-center bg-cover"
                        :style="`background-image: url('${(postInfo || post).user.profile_photo_url}')`"
                    ></div>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold">{{ (postInfo || post).user.name }}</span>
                            <span class="text-xs text-slate-500">{{ (postInfo || post).created_at }}</span>
                        </div>
                        <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                            {{ post.caption }}
                        </p>
                    </div>
                </div>

                <!-- Loading skeleton -->
                <div v-if="isLoading" class="flex flex-col gap-4 p-4">
                    <div v-for="i in 3" :key="i" class="flex items-start gap-3 animate-pulse">
                        <div class="h-9 w-9 rounded-full bg-slate-200 dark:bg-slate-700 shrink-0"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div>
                            <div class="h-3 w-48 rounded bg-slate-200 dark:bg-slate-700"></div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else-if="!isLoading && comments.length === 0" class="flex flex-col items-center justify-center py-14 gap-3">
                    <span class="material-symbols-outlined text-5xl text-slate-300">mode_comment</span>
                    <p class="text-sm text-slate-400">No comments yet. Be the first!</p>
                </div>

                <!-- Comments List -->
                <div v-else class="flex flex-col">
                    <div v-for="comment in comments" :key="comment.id">
                        <!-- Comment -->
                        <div class="flex items-start gap-3 px-4 pt-4 pb-1">
                            <div
                                class="h-9 w-9 shrink-0 rounded-full bg-primary/10 bg-center bg-cover"
                                :style="`background-image: url('${comment.user.profile_photo_url}')`"
                            ></div>
                            <div class="flex-1 flex flex-col gap-0.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold">{{ comment.user.name }}</span>
                                        <span class="text-xs text-slate-500">{{ comment.created_at }}</span>
                                    </div>
                                    <!-- Like button -->
                                    <button @click="handleLikeComment(comment)" class="flex flex-col items-center transition-transform active:scale-90">
                                        <span
                                            class="material-symbols-outlined text-base transition-colors"
                                            :class="comment.is_liked ? 'text-primary' : 'text-slate-400'"
                                            :style="comment.is_liked ? `font-variation-settings: 'FILL' 1` : ''"
                                        >favorite</span>
                                    </button>
                                </div>
                                <p class="text-sm">{{ comment.content }}</p>
                                <div class="flex gap-4 mt-1">
                                    <button @click="startReply(comment)" class="text-xs font-bold text-slate-500 hover:text-primary">Reply</button>
                                    <span v-if="comment.likes_count > 0" class="text-xs text-slate-500">
                                        {{ comment.likes_count }} {{ comment.likes_count === 1 ? 'like' : 'likes' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0">
                            <div
                                v-for="reply in comment.replies"
                                :key="reply.id"
                                class="flex items-start gap-3 px-4 pt-3 pb-1 pl-14"
                            >
                                <div
                                    class="h-8 w-8 shrink-0 rounded-full bg-primary/10 bg-center bg-cover"
                                    :style="`background-image: url('${reply.user.profile_photo_url}')`"
                                ></div>
                                <div class="flex-1 flex flex-col gap-0.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold">{{ reply.user.name }}</span>
                                            <span class="text-xs text-slate-500">{{ reply.created_at }}</span>
                                        </div>
                                        <button @click="handleLikeComment(reply)" class="flex flex-col items-center transition-transform active:scale-90">
                                            <span
                                                class="material-symbols-outlined text-base transition-colors"
                                                :class="reply.is_liked ? 'text-primary' : 'text-slate-400'"
                                                :style="reply.is_liked ? `font-variation-settings: 'FILL' 1` : ''"
                                            >favorite</span>
                                        </button>
                                    </div>
                                    <p class="text-sm">
                                        <span v-if="reply.content.startsWith('@')" class="text-primary font-medium">{{ reply.content.split(' ')[0] }}</span>
                                        <span v-if="reply.content.startsWith('@')"> {{ reply.content.split(' ').slice(1).join(' ') }}</span>
                                        <span v-else>{{ reply.content }}</span>
                                    </p>
                                    <div class="flex gap-4 mt-1">
                                        <button @click="startReply(comment)" class="text-xs font-bold text-slate-500 hover:text-primary">Reply</button>
                                        <span v-if="reply.likes_count > 0" class="text-xs text-slate-500">
                                            {{ reply.likes_count }} {{ reply.likes_count === 1 ? 'like' : 'likes' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Sticky Footer Input -->
            <footer class="border-t border-primary/10 p-3 pb-5 sm:pb-3 shrink-0 bg-white dark:bg-background-dark">
                <!-- Replying to indicator -->
                <Transition name="slide-down">
                    <div v-if="replyingTo" class="flex items-center justify-between px-1 pb-2 text-xs text-slate-500">
                        <span>Replying to <span class="text-primary font-semibold">@{{ replyingTo.username }}</span></span>
                        <button @click="cancelReply" class="text-slate-400 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </Transition>

                <!-- Emoji bar -->
                <div class="flex items-center gap-1 mb-3">
                    <button
                        v-for="emoji in EMOJIS"
                        :key="emoji"
                        @click="appendEmoji(emoji)"
                        class="text-xl hover:scale-125 active:scale-110 transition-transform"
                    >{{ emoji }}</button>
                </div>

                <!-- Input row -->
                <div class="flex items-center gap-3">
                    <div
                        class="h-9 w-9 shrink-0 rounded-full bg-primary/20 bg-center bg-cover"
                        :style="`background-image: url('${userAvatarUrl}')`"
                    ></div>
                    <div class="relative flex-1">
                        <input
                            ref="commentInputRef"
                            v-model="commentText"
                            type="text"
                            placeholder="Add a comment..."
                            class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-full py-2 px-4 text-sm focus:ring-2 focus:ring-primary/50 transition-all outline-none"
                            @keyup.enter="handleSend"
                        />
                        <button
                            @click="handleSend"
                            :disabled="!commentText.trim() || isSending"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-primary transition-opacity"
                            :class="commentText.trim() && !isSending ? 'opacity-100' : 'opacity-40'"
                        >
                            <span v-if="isSending" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                            <span v-else>Post</span>
                        </button>
                    </div>
                </div>
            </footer>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
