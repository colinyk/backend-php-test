
const { createWebHistory, createRouter } = VueRouter;

//const Todo = () => import('./components/todos/todo.js');
import Todo from './components/todos/todo.js';

const routes = [
 { path: '/', name: 'r_todo', component: Todo, meta: {title: 'Todo List'} },
 { path: '/todo-vue',     name: 'r_todo', component: Todo, meta: {title: 'Todo List'} },
 { path: '/todo',     name: 'r_todo', component: Todo, meta: {title: 'Todo List'} },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;