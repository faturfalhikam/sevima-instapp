# InstaApp

A mobile-first Instagram-like social media application built with **Laravel**, **Vue 3**, and **Inertia.js**.

---

## ✨ Features

- 📸 **Create Post** — Upload multiple images with caption
- 🏠 **Feed** — Browse posts from all users with like & comment support
- 👤 **Profile Page** — View your own posts in grid/list layout
- ✏️ **Edit Profile** — Update name, bio, and profile photo
- 🔍 **User Search** — Live search users by name from the top navigation
- 👥 **Public Profile** — View other users' profiles and follow/unfollow them
- 💬 **Comments** — Comment on posts with nested replies
- ❤️ **Likes** — Like/unlike posts and comments

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 + Jetstream (Sanctum auth) |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS |
| Database | SQLite (dev) |
| Storage | Laravel public disk (`storage/app/public`) |

---

## 🚀 Getting Started

### Requirements
- PHP >= 8.2
- Composer
- Node.js >= 18
- npm

### Installation

```bash
# Clone the repo
git clone https://github.com/faturfalhikam/sevima-instapp.git
cd sevima-instapp

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Create storage symlink
php artisan storage:link
```

### Running Locally

```bash
# Start the Laravel dev server
php artisan serve

# In another terminal, start the Vite dev server
npm run dev
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 📁 Key Files

```
app/Http/Controllers/
├── FeedController.php          # Home feed (paginated)
├── PostController.php          # Create post
├── ProfileController.php       # Own profile page
├── EditProfileController.php   # Edit profile (name, bio, photo)
├── PublicProfileController.php # Other users' profiles + follow toggle
├── UserSearchController.php    # Live user search endpoint
└── CommentController.php       # Comments & replies

resources/js/
├── Pages/
│   ├── Dashboard.vue           # Home feed
│   ├── CreatePost.vue          # New post form
│   ├── UserProfile.vue         # Own profile
│   ├── PublicProfile.vue       # Other user profiles
│   └── EditProfile.vue         # Edit profile form
└── Components/
    ├── TopNavigation.vue       # Top bar + search modal
    ├── BottomNavigation.vue    # Bottom tab bar
    ├── PostCard.vue            # Feed post card
    └── CommentModal.vue        # Comments modal
```

---

## 🔗 Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Home feed |
| GET/POST | `/posts/create` | Create new post |
| GET | `/profile` | Own profile |
| GET/POST | `/profile/edit` | Edit profile |
| GET | `/users/search?q=` | Search users (JSON) |
| GET | `/users/{user}` | Public profile |
| POST | `/users/{user}/follow` | Follow / unfollow toggle |

---

## 📄 License

MIT
