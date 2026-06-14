# WELL HEALTH - Restaurant Menu API & Frontend

Full-stack application for restaurant menu management with real-time data synchronization across all devices using MongoDB, Node.js, Express, and Vercel.

## 🚀 Quick Start

### Prerequisites
- Node.js (v14 or higher)
- npm or yarn
- MongoDB Atlas account (free)
- Vercel account (free)
- Git account (GitHub)

### Local Development

1. **Install dependencies:**
```bash
cd api
npm install
```

2. **Configure environment:**
- Copy `.env` file (already configured)
- Update `MONGODB_URI` if needed

3. **Start server locally:**
```bash
npm run dev
```
Server runs on `http://localhost:5000`

### Frontend Setup

1. Open `index.html` or `admin.html` in browser
2. Update API endpoint in JavaScript:
```javascript
const API_URL = 'http://localhost:5000/api';
```

## 📦 API Endpoints

### Items
- `GET /api/items` - Get all items
- `POST /api/items` - Create item
- `PUT /api/items/:id` - Update item
- `DELETE /api/items/:id` - Delete item

### Categories
- `GET /api/categories` - Get all categories
- `POST /api/categories` - Create category
- `PUT /api/categories/:id` - Update category
- `DELETE /api/categories/:id` - Delete category

### Guidelines
- `GET /api/guidelines` - Get all guidelines
- `POST /api/guidelines` - Create guideline
- `PUT /api/guidelines/:id` - Update guideline
- `DELETE /api/guidelines/:id` - Delete guideline

### Social Links
- `GET /api/social-links` - Get all social links
- `POST /api/social-links` - Create social link
- `PUT /api/social-links/:id` - Update social link
- `DELETE /api/social-links/:id` - Delete social link

### Settings
- `GET /api/settings` - Get all settings
- `POST /api/settings` - Update settings

## 🌐 Deploy to Vercel

1. **Push to GitHub:**
```bash
git init
git add .
git commit -m "Initial commit"
git push origin main
```

2. **Connect Vercel:**
- Go to https://vercel.com
- Click "New Project"
- Import your GitHub repository
- Add environment variable:
  - `MONGODB_URI` = your MongoDB connection string

3. **Deploy:**
- Click "Deploy"
- Vercel will automatically build and deploy

## 🔒 Environment Variables

For production (Vercel):
```
MONGODB_URI=mongodb+srv://user:password@cluster0.xxxxx.mongodb.net/dbname
PORT=5000
NODE_ENV=production
```

## 📱 Features

- ✅ Real-time data synchronization
- ✅ Multi-device support
- ✅ Bilingual (English/Arabic) with RTL support
- ✅ Menu management with categories
- ✅ Discount system (percentage & fixed)
- ✅ Nutritional information tracking
- ✅ Health guidelines management
- ✅ Social media links management
- ✅ Image upload & management
- ✅ Admin dashboard with full CRUD operations

## 🛠 Technology Stack

- **Frontend:** HTML5, Tailwind CSS, Vanilla JavaScript
- **Backend:** Node.js, Express.js
- **Database:** MongoDB Atlas
- **Hosting:** Vercel
- **Bilingual:** Arabic (RTL) & English (LTR)

## 📝 License

MIT License

## 👤 Author

WELL HEALTH Team
