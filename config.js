// central configuration file for WELL HEALTH website
const CONFIG = {
  // 1. رابط خادم الباك إند (API URL)
  // إذا كنت تستخدم استضافة مشتركة (Shared Hosting) وقمت برفع الباك إند على Render/Railway مثلاً،
  // ضع رابط الباك إند هنا، مثال: 'https://well-health-api.onrender.com'
  // إذا كان الباك إند والواجهة الأمامية مرفوعين على نفس الخادم (VPS) اترك القيمة فارغة ''
  API_BASE: '',
};

// وظيفة تلقائية لتحديد مسار الطلبات
function getApiBase() {
  if (CONFIG.API_BASE) {
    return CONFIG.API_BASE;
  }
  // إذا تم فتح الملف محلياً بصيغة file://، نستخدم الـ localhost للباك إند
  return window.location.origin.indexOf('file:') === 0 ? 'http://localhost:5000' : '';
}
