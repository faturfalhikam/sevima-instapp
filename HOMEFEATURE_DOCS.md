# InstaApp - Home Feed Implementation

## ✅ Apa yang Telah Dibuat

### 1. **Database Migrations** 📊
Telah membuat 5 migration files untuk membuat table di database:

| Table | Deskripsi |
|-------|-----------|
| `posts` | Menyimpan data postingan (user_id, caption, location) |
| `post_images` | Tempat menyimpan gambar-gambar postingan |
| `likes` | Menyimpan data like dari pengguna ke postingan |
| `comments` | Menyimpan komentar di postingan |
| `followers` | Menyimpan relationship follower/following antar user |

**Location**: `/database/migrations/`

### 2. **Models & Relationships** 🔗
Telah membuat 5 model dengan relationships yang lengkap:

- **Post Model**
  - `user()` - Belongs to User
  - `images()` - Has many PostImage
  - `likes()` - Has many Like
  - `comments()` - Has many Comment
  - Methods: `isLikedBy()`, `likesCount()`, `commentsCount()`

- **PostImage Model**
  - `post()` - Belongs to Post

- **Like Model**
  - `user()` - Belongs to User
  - `post()` - Belongs to Post

- **Comment Model**
  - `user()` - Belongs to User
  - `post()` - Belongs to Post

- **Follower Model**
  - `follower()` - Belongs to User
  - `following()` - Belongs to User

- **User Model** (Updated)
  - `posts()` - Has many Post
  - `likes()` - Has many Like
  - `comments()` - Has many Comment
  - `followers()` - Has many Follower (followers dari user ini)
  - `following()` - Has many Follower (user yang di-follow)
  - Methods: `isFollowing()`, `isFollowedBy()`, `followersCount()`, `followingCount()`

**Location**: `/app/Models/`

### 3. **Controller & API Endpoints** 🚀
**FeedController** dengan methods:
- `index()` - Menampilkan halaman Dashboard
- `getFeed()` - API endpoint untuk get feed posts dengan pagination (infinite scroll)
- `likePost()` - API endpoint untuk toggle like
- `addComment()` - API endpoint untuk tambah komentar
- `followUser()` - API endpoint untuk follow/unfollow user

**Location**: `/app/Http/Controllers/FeedController.php`

**API Routes**:
```
GET  /api/feed                  - Get feed posts
POST /api/posts/{id}/like       - Like a post
POST /api/posts/{id}/comments   - Add comment
POST /api/users/{id}/follow     - Follow user
```

**Web Routes**:
```
GET /dashboard - Show home feed
```

### 4. **Vue Components** 💻
Telah membuat 4 Vue components:

- **Dashboard.vue** (Main Home Page)
  - Infinite scroll functionality dengan Intersection Observer
  - Fetch posts dari API
  - Handle like dan comment
  - Loading indicator

- **PostCard.vue** (Individual Post Component)
  - Display post dengan gambar
  - Image navigation (previous/next)
  - Action buttons (like, comment, share, bookmark)
  - Comments list
  - Comment input form
  - Like counter
  - User info

- **TopNavigation.vue** (Top Navigation Bar)
  - Logo dan app title
  - Notification bell dengan counter

- **BottomNavigation.vue** (Bottom Navigation Bar)
  - Home link
  - Create post link
  - Profile link

**Location**: `/resources/js/Pages/Dashboard.vue` dan `/resources/js/Components/`

### 5. **Features** ⭐
✅ **Infinite Scroll** - Otomatis load postingan baru saat scroll ke bawah
✅ **Like Functionality** - Toggle like dengan update counter
✅ **Comment Functionality** - Tambah komentar ke postingan
✅ **Follow System** - Follow/unfollow users
✅ **Image Gallery** - Navigation antar gambar dalam postingan
✅ **Feed Filter** - Hanya show postingan dari user yang di-follow
✅ **Dark Mode Support** - Custom colors untuk light dan dark theme

### 6. **Database Seeders** 🌱
**PostSeeder** membuat:
- 5 test users
- 3 posts per user
- 1-2 images per post
- 2-5 comments per post
- 3-10 likes per post
- Follow relationships antar users

**Location**: `/database/seeders/PostSeeder.php`

## 📋 Testing untuk Verify Semua Berfungsi

### 1. Login
```bash
Email: test@example.com
Password: password
```

### 2. Navigasi ke Home/Dashboard
Buka browser dan akses `http://localhost:8000/dashboard`

### 3. Verify Infinite Scroll
- Scroll ke bawah halaman
- Lihat loading indicator
- Postingan baru akan muncul otomatis

### 4. Test Like
- Klik tombol heart pada postingan
- Heart akan berubah warna menjadi pink (#f4258c)
- Like counter akan bertambah

### 5. Test Comment
- Klik tombol comment
- Input form akan muncul
- Ketik komentar dan tekan Enter atau klik Post
- Komentar akan muncul di list

### 6. Test Image Navigation
- Postingan dengan multiple images akan menunjukkan navigation buttons
- Klik prev/next untuk berpindah gambar
- Atau klik indicator dot untuk jump ke image specific

## 🗂️ File Structure
```
app/
├── Models/
│   ├── Post.php ..................... Post model dengan relationships
│   ├── PostImage.php ................ PostImage model
│   ├── Like.php ..................... Like model
│   ├── Comment.php .................. Comment model
│   ├── Follower.php ................. Follower model
│   └── User.php (Updated) ........... User model dengan relationships
├── Http/
│   └── Controllers/
│       └── FeedController.php ........ Feed controller dengan API methods

database/
├── migrations/
│   ├── 2026_03_14_154936_create_posts_table.php
│   ├── 2026_03_14_154937_create_post_images_table.php
│   ├── 2026_03_14_154937_create_likes_table.php
│   ├── 2026_03_14_154937_create_comments_table.php
│   └── 2026_03_14_154938_create_followers_table.php
├── factories/
│   ├── PostFactory.php
│   ├── PostImageFactory.php
│   ├── LikeFactory.php
│   ├── CommentFactory.php
│   └── FollowerFactory.php
└── seeders/
    ├── PostSeeder.php .............. Post seeder dengan dummy data
    └── DatabaseSeeder.php (Updated)

resources/
└── js/
    ├── Pages/
    │   └── Dashboard.vue ........... Main home feed page
    └── Components/
        ├── PostCard.vue ........... Individual post component
        ├── TopNavigation.vue ....... Top navigation bar
        └── BottomNavigation.vue ... Bottom navigation bar

routes/
├── api.php (Updated) .............. API routes untuk feed
└── web.php (Updated) .............. Web routes dengan FeedController
```

## 🚀 Cara Menjalankan

### 1. Setup database
```bash
php artisan migrate:fresh --seed
```

### 2. Start Laravel development server
```bash
php artisan serve
```

### 3. Start Vite (di terminal lain)
```bash
npm run dev
```

### 4. Akses aplikasi
Buka browser: `http://localhost:8000`

Login dengan:
- Email: `test@example.com`
- Password: `password`

## 💡 Cara Extend/Customize

### Menambah Lebih Banyak Dummy Data
Edit `/database/seeders/PostSeeder.php` dan ubah jumlah users/posts:
```php
$users = \App\Models\User::factory(10)->create(); // Dari 5 menjadi 10
```

### Mengubah Design
- Edit `/resources/js/Components/PostCard.vue` untuk styling
- Edit `/tailwind.config.js` untuk custom colors

### Menambah Features Baru
- Update routes di `/routes/api.php`
- Tambah method baru di `FeedController`
- Update component Vue sesuai kebutuhan

## ⚠️ Notes
- Semua image URLs menggunakan placeholder path yang bisa di-update dengan image upload functionality
- Token authentication menggunakan Sanctum (sudah default di Jetstream)
- Design menggunakan Material Symbols untuk icons
- Color scheme: Primary pink (#f4258c), Dark background (#221019)
