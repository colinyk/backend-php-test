/**
 * 
 * TASK 6: display todo list with VueJs page
 */

import router from './router3.js';

const app3 = Vue.createApp({});
app3.use(router).mount('#vueapp');

/**
 * Declare global variable
 * @type type
 */
app3.config.globalProperties.$http = axios
// app3.config.globalProperties.moment = moment




