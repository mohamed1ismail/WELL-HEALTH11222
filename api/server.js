const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '.env') });

const app = express();

// Middleware
app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb' }));

// MongoDB Connection (with caching for Vercel serverless)
let isConnected = false;

async function connectDB() {
    if (isConnected && mongoose.connection.readyState === 1) return;
    try {
        await mongoose.connect(process.env.MONGODB_URI, {
            useNewUrlParser: true,
            useUnifiedTopology: true,
            serverSelectionTimeoutMS: 10000,
            socketTimeoutMS: 45000,
        });
        isConnected = true;
        console.log('✅ MongoDB connected successfully');
    } catch (err) {
        console.error('❌ MongoDB connection error:', err.message);
        isConnected = false;
        throw err;
    }
}

// Connect on startup
connectDB().catch(console.error);

// Middleware to ensure DB connection on each request (important for Vercel serverless)
app.use(async (req, res, next) => {
    try {
        await connectDB();
        next();
    } catch (err) {
        res.status(503).json({ error: 'Database connection failed. Please try again.' });
    }
});

// Define Schemas
const itemSchema = new mongoose.Schema({
    id: String,
    nameEn: String,
    nameAr: String,
    descEn: String,
    descAr: String,
    category: String,
    price: String,
    priceSm: String,
    priceMd: String,
    priceLg: String,
    img: String,
    cal: String,
    protein: String,
    carbs: String,
    fats: String,
    discountType: String,
    discountValue: Number,
    imageSize: { type: String, default: 'medium' },
    visible: { type: Boolean, default: true },
    portions: [{ label: String, price: String }],
    createdAt: { type: Date, default: Date.now }
});

const categorySchema = new mongoose.Schema({
    id: String,
    nameEn: String,
    nameAr: String,
    icon: String,
    createdAt: { type: Date, default: Date.now }
});

const guidelineSchema = new mongoose.Schema({
    emoji: String,
    titleEn: String,
    titleAr: String,
    descEn: String,
    descAr: String,
    createdAt: { type: Date, default: Date.now }
});

const socialLinkSchema = new mongoose.Schema({
    label: String,
    url: String,
    icon: String,
    color: String,
    createdAt: { type: Date, default: Date.now }
});

const settingsSchema = new mongoose.Schema({
    captionEn: String,
    captionAr: String,
    bannerEnabled: Boolean,
    bannerText: String,
    menuUrl: String,
    appDownloadEnabled: Boolean,
    androidAppUrl: String,
    iosAppUrl: String,
    logo: String,
    coverImage: String,
    aboutTextEn: String,
    aboutTextAr: String,
    adminUsername: { type: String, default: 'admin' },
    adminPassword: { type: String, default: 'wellhealth123' },
    updatedAt: { type: Date, default: Date.now }
});

// Create Models
const Item = mongoose.model('Item', itemSchema);
const Category = mongoose.model('Category', categorySchema);
const Guideline = mongoose.model('Guideline', guidelineSchema);
const SocialLink = mongoose.model('SocialLink', socialLinkSchema);
const Settings = mongoose.model('Settings', settingsSchema);

// =====================================================
// ITEMS API
// =====================================================

// Get all items
app.get('/api/items', async (req, res) => {
    try {
        const items = await Item.find();
        res.json(items);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Get single item
app.get('/api/items/:id', async (req, res) => {
    try {
        const item = await Item.findById(req.params.id);
        res.json(item);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Create item
app.post('/api/items', async (req, res) => {
    try {
        const item = new Item(req.body);
        await item.save();
        res.json(item);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Update item
app.put('/api/items/:id', async (req, res) => {
    try {
        const item = await Item.findByIdAndUpdate(req.params.id, req.body, { new: true });
        res.json(item);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Delete item
app.delete('/api/items/:id', async (req, res) => {
    try {
        await Item.findByIdAndDelete(req.params.id);
        res.json({ message: 'Item deleted' });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// CATEGORIES API
// =====================================================

app.get('/api/categories', async (req, res) => {
    try {
        const categories = await Category.find();
        res.json(categories);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/categories', async (req, res) => {
    try {
        const category = new Category(req.body);
        await category.save();
        res.json(category);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.put('/api/categories/:id', async (req, res) => {
    try {
        const category = await Category.findByIdAndUpdate(req.params.id, req.body, { new: true });
        res.json(category);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/api/categories/:id', async (req, res) => {
    try {
        await Category.findByIdAndDelete(req.params.id);
        res.json({ message: 'Category deleted' });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// GUIDELINES API
// =====================================================

app.get('/api/guidelines', async (req, res) => {
    try {
        const guidelines = await Guideline.find();
        res.json(guidelines);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/guidelines', async (req, res) => {
    try {
        const guideline = new Guideline(req.body);
        await guideline.save();
        res.json(guideline);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.put('/api/guidelines/:id', async (req, res) => {
    try {
        const guideline = await Guideline.findByIdAndUpdate(req.params.id, req.body, { new: true });
        res.json(guideline);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/api/guidelines/:id', async (req, res) => {
    try {
        await Guideline.findByIdAndDelete(req.params.id);
        res.json({ message: 'Guideline deleted' });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// SOCIAL LINKS API
// =====================================================

app.get('/api/social-links', async (req, res) => {
    try {
        const links = await SocialLink.find();
        res.json(links);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/social-links', async (req, res) => {
    try {
        const link = new SocialLink(req.body);
        await link.save();
        res.json(link);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.put('/api/social-links/:id', async (req, res) => {
    try {
        const link = await SocialLink.findByIdAndUpdate(req.params.id, req.body, { new: true });
        res.json(link);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/api/social-links/:id', async (req, res) => {
    try {
        await SocialLink.findByIdAndDelete(req.params.id);
        res.json({ message: 'Social link deleted' });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// SETTINGS API
// =====================================================

app.get('/api/settings', async (req, res) => {
    try {
        const settings = await Settings.findOne().select('-adminPassword');
        res.json(settings || {});
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Get admin credentials
app.get('/api/admin/credentials', async (req, res) => {
    try {
        const settings = await Settings.findOne();
        res.json({
            username: (settings && settings.adminUsername) || 'admin',
            password: (settings && settings.adminPassword) || 'wellhealth123'
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/settings', async (req, res) => {
    try {
        let settings = await Settings.findOne();
        if (settings) {
            settings = await Settings.findByIdAndUpdate(settings._id, req.body, { new: true });
        } else {
            settings = new Settings(req.body);
            await settings.save();
        }
        res.json(settings);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// BULK SYNC API
// =====================================================

app.post('/api/items/sync', async (req, res) => {
    try {
        const items = Array.isArray(req.body) ? req.body : [];
        await Item.deleteMany({});
        const saved = items.length ? await Item.insertMany(items) : [];
        res.json(saved);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/categories/sync', async (req, res) => {
    try {
        const categories = Array.isArray(req.body) ? req.body : [];
        await Category.deleteMany({});
        const saved = categories.length ? await Category.insertMany(categories) : [];
        res.json(saved);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/guidelines/sync', async (req, res) => {
    try {
        const guidelines = Array.isArray(req.body) ? req.body : [];
        await Guideline.deleteMany({});
        const saved = guidelines.length ? await Guideline.insertMany(guidelines) : [];
        res.json(saved);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/social-links/sync', async (req, res) => {
    try {
        const links = Array.isArray(req.body) ? req.body : [];
        await SocialLink.deleteMany({});
        const saved = links.length ? await SocialLink.insertMany(links) : [];
        res.json(saved);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// =====================================================
// START SERVER
// =====================================================

if (require.main === module) {
    const PORT = process.env.PORT || 5000;
    app.listen(PORT, () => {
    console.log(`🚀 Server running on port ${PORT}`);
    });
}

module.exports = app;
