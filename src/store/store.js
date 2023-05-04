import Vue from 'vue'
import Vuex from 'vuex'
import { NewsLettersApi } from './NewsLettersApi'
Vue.use(Vuex)

const newsLettersApi = new NewsLettersApi()

export default new Vuex.Store({
	state: {
		newsletters: [],
		activeNewsLetterId: null,
	},
	mutations: {
		setNewsletters(state, newsletters) {
			state.newsletters = newsletters
		},
		setActiveNewsLetterId(state, activeNewsLetterId) {
			state.activeNewsLetterId = activeNewsLetterId
		},
	},
	actions: {
		async svrNewsletters({ dispatch, state, commit }) {
			const newsletters = await newsLettersApi.getNewsLetters()
			commit('setNewsletters', newsletters)
			if (state.activeFicheId === null && Array.isArray(newsletters) && newsletters.length >= 0) {
				commit('setActiveNewsLetterId', 0)
			}
		},
	},
	getters: {
		getNewsletters: state => (state.newsletters) ? state.newsletters : [],
		getNewsletter: state => (state.newsletters && state.newsletters[state.activeNewsLetterId]) ? state.fiches[state.activeNewsLetterId] : null,
	},

})
