import ui from '@nuxt/ui/vue-plugin'
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import renderer from '../renderer.vue'
import { initializeExceptionStore } from '../store'
import './style.css'

const app = createApp(renderer)
const router = createRouter({
	routes: [],
	history: createWebHistory(),
})

const element = document.getElementById('tempest-hydration')
if (!element) {
	throw new Error('Hydration element not found')
}

const hydration = JSON.parse(element.textContent!)
initializeExceptionStore(hydration)

app.use(router)
app.use(ui)
app.mount('#root')
