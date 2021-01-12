/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import { HasError, AlertError } from 'vform'
import Swal from 'sweetalert2'
import CKEditor from 'ckeditor4-vue';
// import CKEditor from '@ckeditor/ckeditor5-vue'
import VueRouter from 'vue-router'
import VueProgressBar from 'vue-progressbar'
import Multiselect from 'vue-multiselect'

Vue.use( CKEditor );
Vue.use(VueRouter);
Vue.use(VueProgressBar, {
  color: 'rgb(143, 255, 199)',
  failedColor: 'red',
  height: '3px'
});

window.Swal = Swal;
window.Fire = new Vue();

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  onOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
})


window.Toast = Toast;

Vue.mixin({
  data: function() {
    return {
      // siteUrl:'http://localhost/hosp/'
      siteUrl: window.location.origin + '/lahore/'
    }
  }
})

Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.component('pagination', require('laravel-vue-pagination'));
// register globally
Vue.component('multiselect', Multiselect);

let routes = [ 
  // admin routes
    { path: '/lahore/admin', name: 'index', component: require('./components/admin/Index.vue').default },
    { path: '/lahore/admin/pages', name: 'pages', component: require('./components/admin/Page.vue').default },
    { path: '/lahore/admin/changepassword', name: 'changepassword', component: require('./components/admin/ChangePassword.vue').default },
    { path: '/lahore/admin/blog-category', name: 'blogcategory', component: require('./components/admin/BlogCategory.vue').default },
    { path: '/lahore/admin/blog-author', name: 'blogauthor', component: require('./components/admin/BlogAuthor.vue').default },
    { path: '/lahore/admin/blog', name: 'blog', component: require('./components/admin/Blog.vue').default },
    { path: '/lahore/admin/seo-tag', name: 'seo', component: require('./components/admin/SeoTag.vue').default },
    { path: '/lahore/admin/inquiry', name: 'inquiry', component: require('./components/admin/Inquiry.vue').default },
    { path: '/lahore/admin/watch-enquiry', name: 'watchEnquiry', component: require('./components/admin/WatchEnquiry.vue').default },
    { path: '/lahore/admin/gender', name: 'gender', component: require('./components/admin/Gender.vue').default },
    { path: '/lahore/admin/brand', name: 'brand', component: require('./components/admin/Brand.vue').default },
    { path: '/lahore/admin/collection', name: 'collection', component: require('./components/admin/Collection.vue').default },
    { path: '/lahore/admin/movement', name: 'movement', component: require('./components/admin/Movement.vue').default },
    { path: '/lahore/admin/movement-type', name: 'movementtype', component: require('./components/admin/MovementType.vue').default },
    { path: '/lahore/admin/case-size', name: 'casesize', component: require('./components/admin/CaseSize.vue').default },
    { path: '/lahore/admin/case-shape', name: 'caseshape', component: require('./components/admin/CaseShape.vue').default },
    { path: '/lahore/admin/case-material', name: 'casematerial', component: require('./components/admin/CaseMaterial.vue').default },
    { path: '/lahore/admin/glass-material', name: 'glassmaterial', component: require('./components/admin/GlassMaterial.vue').default },
    { path: '/lahore/admin/dial-colour', name: 'dialcolour', component: require('./components/admin/DialColour.vue').default },
    { path: '/lahore/admin/strap-material', name: 'strapmaterial', component: require('./components/admin/StrapMaterial.vue').default },
    { path: '/lahore/admin/strap-colour', name: 'strapcolour', component: require('./components/admin/StrapColour.vue').default },
    { path: '/lahore/admin/feature', name: 'feature', component: require('./components/admin/Feature.vue').default },
    { path: '/lahore/admin/product-detail', name: 'productdetail', component: require('./components/admin/ProductDetail.vue').default },
    { path: '/lahore/admin/discount', name: 'discount', component: require('./components/admin/Discount.vue').default },
    { path: '/lahore/admin/main-category', name: 'maincategory', component: require('./components/admin/MainCategory.vue').default },
    { path: '/lahore/admin/colour', name: 'colour', component: require('./components/admin/Colour.vue').default },
    { path: '/lahore/admin/clock-type', name: 'clocktype', component: require('./components/admin/ClockType.vue').default },
    { path: '/lahore/admin/banner-heading', name: 'bannerheading', component: require('./components/admin/BannerHeading.vue').default },
    { path: '/lahore/admin/orders', name: 'orders', component: require('./components/admin/orders.vue').default },
    { path: '/lahore/admin/customers', name: 'customers', component: require('./components/admin/customers.vue').default }
  ]

const router = new VueRouter({
  mode: 'history',
  routes // short for `routes: routes`
})

Vue.filter('formatDateBigMonth', function(value) {
  if (value) {
    return moment(String(value)).format('DD MMM, YYYY');
  }
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/admin/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


const app = new Vue({
  router
}).$mount('#app')
