<template>
	<!--
    SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
    SPDX-License-Identifier: AGPL-3.0-or-later
    -->
	<div id="content" class="app-newsletterbuilder">
		<NcAppNavigation>
			<NcAppNavigationNew v-if="!loading"
				:text="t('newsletterbuilder', 'Nouvelle Newsletter')"
				:disabled="false"
				button-id="new-newsletterbuilder-button"
				button-class="icon-add"
				@click="newNewsLetter" />
			<ul>
				<NcAppNavigationItem v-for="newsletter in newsletters"
					:key="newsletter.id"
					:title="newsletter.title ? newsletter.title : t('newsletterbuilder', 'Nouvelle NewsLetter')"
					:class="{active: currentNewsLetterId === newsletter.id}"
					@click="openNewsLetter(newsletter)">
					<template slot="actions">
						<ActionButton v-if="newsletter.id === -1"
							icon="icon-close"
							@click="cancelNewNewsLetter(newsletter)">
							{{
								t('newsletterbuilder', 'Cancel newsletter creation') }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteNewsLetter(newsletter)">
							{{
								t('newsletterbuilder', 'Delete newsletter') }}
						</ActionButton>
					</template>
				</NcAppNavigationItem>
			</ul>
		</NcAppNavigation>
		<NcAppContent>
			<div v-if="currentNewsLetter">
				<input ref="title"
					v-model="currentNewsLetter.title"
					type="text"
					:disabled="updating">
				<textarea ref="content" v-model="currentNewsLetter.content" :disabled="updating" />
				<input type="button"
					class="primary"
					:value="t('newsletterbuilder', 'Save')"
					:disabled="updating || !savePossible"
					@click="saveNewsLetter">
			</div>
			<div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>
					{{
						t('newsletterbuilder', 'Create a newsletter to get started') }}
				</h2>
			</div>
		</NcAppContent>
	</div>
</template>

<script>
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'

import { mapState, mapGetters, mapActions } from 'vuex'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
	},
	data() {
		return {
			// newsletters: [],
			currentNewsLetterId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		...mapState(['newsletters']),
		...mapGetters(['getNewsLetter', 'getNewsLetters']),
		/**
		 * Return the currently selected newsletter object
		 *
		 * @return {object | null}
		 */
		currentNewsLetter() {
			if (this.currentNewsLetterId === null) {
				return null
			}
			return this.newsletters.find((newsletter) => newsletter.id === this.currentNewsLetterId)
		},

		/**
		 * Returns true if a newsletter is selected and its title is not empty
		 *
		 * @return {boolean}
		 */
		savePossible() {
			return this.currentNewsLetter && this.currentNewsLetter.title !== ''
		},
	},
	/**
	 * Fetch list of newsletters when the component is loaded
	 */
	async mounted() {
		try {
			await this.$store.dispatch('svrNewsletters')
		} catch (e) {
			console.error(e)
			showError(t('newsletterstutorial', 'Could not fetch newsletters'))
		}
		this.loading = false
	},

	methods: {
		/**
		 * Create a new newsletter and focus the newsletter content field automatically
		 *
		 * @param {object} newsletter NewsLetter object
		 */
		openNewsLetter(newsletter) {
			if (this.updating) {
				return
			}
			this.currentNewsLetterId = newsletter.id
			this.$nextTick(() => {
				this.$refs.content.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new newsletter or save
		 */
		saveNewsLetter() {
			if (this.currentNewsLetterId === -1) {
				// this.createNewsLetter(this.currentNewsLetter)
			} else {
				// this.updateNewsLetter(this.currentNewsLetter)
			}
		},
		/**
		 * Create a new newsletter and focus the newsletter content field automatically
		 * The newsletter is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newNewsLetter() {
			if (this.currentNewsLetterId !== -1) {
				this.currentNewsLetterId = -1
				this.newsletters.push({
					id: -1,
					title: '',
					content: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		/**
		 * Abort creating a new newsletter
		 */
		cancelNewNewsLetter() {
			this.newsletters.splice(this.newsletters.findIndex((newsletter) => newsletter.id === -1), 1)
			this.currentNewsLetterId = null
		},
		/**
		 * Create a new newsletter by sending the information to the server
		 *
		 * @param {object} newsletter NewsLetter object
		 */
		async createNewsLetter(newsletter) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/newsletterbuilder/newsletters'), newsletter)
				const index = this.newsletters.findIndex((match) => match.id === this.currentNewsLetterId)
				this.$set(this.newsletters, index, response.data)
				this.currentNewsLetterId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('newsletterstutorial', 'Could not create the newsletter'))
			}
			this.updating = false
		},
		/**
		 * Update an existing newsletter on the server
		 *
		 * @param {object} newsletter NewsLetter object
		 */
		async updateNewsLetter(newsletter) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/newsletterbuilder/newsletters/${newsletter.id}`), newsletter)
			} catch (e) {
				console.error(e)
				showError(t('newsletterstutorial', 'Could not update the newsletter'))
			}
			this.updating = false
		},
		/**
		 * Delete a newsletter, remove it from the frontend and show a hint
		 *
		 * @param {object} newsletter NewsLetter object
		 */
		async deleteNewsLetter(newsletter) {
			try {
				await axios.delete(generateUrl(`/apps/newsletterbuilder/newsletters/${newsletter.id}`))
				this.newsletters.splice(this.newsletters.indexOf(newsletter), 1)
				if (this.currentNewsLetterId === newsletter.id) {
					this.currentNewsLetterId = null
				}
				showSuccess(t('newsletterbuilder', 'NewsLetter deleted'))
			} catch (e) {
				console.error(e)
				showError(t('newsletterbuilder', 'Could not delete the newsletter'))
			}
		},
	},
}
</script>
<style scoped>
	#app-content > div {
		width: 100%;
		height: 100%;
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
