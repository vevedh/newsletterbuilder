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
			<div v-show="currentNewsLetter && myEditor" id="emptycontentEditor">
				<div id="gjs"  class="gjs-editor-cont">
				<mjml>
					<mj-body>
					<mj-section> </mj-section>
					</mj-body>	
				</mjml>
				</div>
				<!--<input ref="title"
					v-model="currentNewsLetter.title"
					type="text"
					:disabled="updating">
				<textarea ref="content" v-model="currentNewsLetter.content" :disabled="updating" />
				<input type="button"
					class="primary"
					:value="t('newsletterbuilder', 'Save')"
					:disabled="updating || !savePossible"
					@click="saveNewsLetter">-->
			</div>
			<div v-show="!currentNewsLetter" id="emptycontent">
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

import 'grapesjs/dist/css/grapes.min.css'
import grapesJS from 'grapesjs'
import fr from 'grapesjs/locale/fr'
import grapesJSMJML from 'grapesjs-mjml'
import mjmlFR from 'grapesjs-mjml/locale/fr' 


import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'

import { mapState, mapGetters, mapActions } from 'vuex'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import {
  getRequestToken,
  getCurrentUser,
  onRequestTokenUpdate,
} from '@nextcloud/auth'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
//import mjml from 'mjml'


export default {
	name: 'App',
	components: {
		NcActionButton,
		NcAppContent,
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationNew,
		//mjml,
	},
	data() {
		return {
			// newsletters: [],
			myEditor:null,
			showBuilder: false,
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
		this.loading = true
		console.log('App OC:', OC)
		console.log('App OCA:', OCA)
		console.log('App OCP:', OCP)
		console.log('Window :', window)
		console.log('Window :', this.$refs)
		console.log('User infos :', OC.getCurrentUser())

			
		try {
			await this.$store.dispatch('svrNewsletters')
		} catch (e) {
			console.error(e)
			showError(t('newsletterstutorial', 'Could not fetch newsletters'))
		}
		
		
		this.loading = false
	},

	methods: {
		initEditor() {
					this.myEditor = grapesJS.init({
					//clearOnRender: true,
					//height: '100%',
					allowScripts: 1,
					avoidInlineStyle: true,
					storageManager: {
						options: {
							local: { key: 'gjsProjectMjml' }
						}
					},
					storageManager: false,
					//storageManager:{ autoload: 0 },
					/*assetManager: {
					assets: images,
					upload: 0,
					uploadText: 'Uploading is not available in this demo',
					},*/
					fromElement: true,
					container: '#gjs',
					i18n: {
								// locale: 'en', // default locale
								// detectLocale: true, // by default, the editor will detect the language
								// localeFallback: 'en', // default fallback
								messages: { fr: fr },
					},
					plugins: [grapesJSMJML],
					pluginsOpts: {
					[grapesJSMJML]: {
						// Optional options
						i18n: { fr: mjmlFR },
						
					}
				},
				});
				console.log('My editor components :',this.myEditor.Panels.getPanel('options').buttons)
				//console.log('My editor components :',this.myEditor.Panels.getButton('options','sw-visibility').set('label','Un test'))	
				const buttonsOrig = Object.assign([],this.myEditor.Panels.getPanel('options').buttons.models)
				const pn = this.myEditor.Panels
				/***********************************************/
				console.log('Button 1 :',buttonsOrig[1])
				const btn1 = buttonsOrig[1]
				const btnPreview = {
					active: btn1.attributes?.active,
					id: btn1.id,
					//className: btn4.className,
					command: btn1.command,
					context: btn1.attributes?.context,
					label: `<svg style="display: block; max-width:22px" viewBox="0 0 24 24"><path fill="currentColor" d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"></path></svg>`,
					attributes: { title:btn1.attributes?.attributes?.title }
				}
				console.log('Button Preview :',btnPreview)
				/***************************************************/
				console.log('Button 2 :',buttonsOrig[2])
				const btn2 = buttonsOrig[2]
				const btnFullScreen = {
					active: btn2.attributes?.active,
					id: btn2.id,
					//className: btn4.className,
					command: btn2.command,
					context: btn2.attributes?.context,
					label: `<svg style="display: block; max-width:22px" viewBox="0 0 24 24">
					<path fill="currentColor" d="M5,5H10V7H7V10H5V5M14,5H19V10H17V7H14V5M17,14H19V19H14V17H17V14M10,17V19H5V14H7V17H10Z"></path>
				</svg>`,
					attributes: { title:btn2.attributes?.attributes?.title }
				}
				console.log('Button FullScreen :',btnFullScreen)
				/************************************************/
				console.log('Button 3 :',buttonsOrig[3])
				const btn3 = buttonsOrig[3]
				const btnCode = {
					active: btn3.attributes?.active,
					id: btn3.id,
					//className: btn4.className,
					command: btn3.command,
					context: btn3.attributes?.context,
					label: `<svg style="display: block; max-width: 22px" viewBox="0 0 24 24">
						<path fill="currentColor" d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z"></path>
					</svg>`,
					attributes: { title:btn3.attributes?.attributes?.title }
				}
				console.log('Button Preview :',btnCode)
				/******************************************/
				console.log('Button 4 :',buttonsOrig[4])
				const btn4 = buttonsOrig[4]
				const newBtn4 = {
					active: btn4.attributes?.active,
					id: btn4.id,
					//className: btn4.className,
					command: btn4?.command,
					context: btn4.attributes?.context,
					label: btn4.attributes?.label,
					attributes: { title:btn4.attributes?.title }
				}
				/**************************************************/
				console.log('Button 5 :',buttonsOrig[5])
				const btn5 = buttonsOrig[5]
				const newBtn5 = {
					active: btn5.attributes?.active,
					id: btn5.id,
					//className: btn4.className,
					command: btn5.command,
					context: btn5.attributes?.context,
					label: btn5.attributes?.label,
					attributes: { title:btn5.attributes?.attributes?.title }
				}
				console.log('Button 5 :',newBtn5)
				/***********************************/
				console.log('Button 6 :',buttonsOrig[6])
				const btn6 = buttonsOrig[6]
				const newBtn6 = {
					active: btn6.attributes?.active,
					id: btn6.id,
					//className: btn4.className,
					command: btn6.command,
					context: btn6.attributes?.context,
					label: btn6.attributes.label,
					attributes: { title:btn6.attributes?.attributes?.title }
				}
				console.log('Button 6 :',newBtn6)
				//const myComponent = this.myEditor.Panels.getButton('options','sw-visibility')
				pn.removeButton('options', 'preview');
				pn.removeButton('options', 'sw-visibility');
				pn.removeButton('options', 'fullscreen');
				pn.removeButton('options', 'export-template');
				pn.removeButton('options', 'mjml-import');
				pn.removeButton('options', 'undo');
				pn.removeButton('options', 'redo');
				pn.addButton('options',{
				active: true,
				id: 'sw-visibility',
				className: 'fa fa-square-o',
				command: 'core:component-outline',
				context: 'sw-visibility',
				label:`<svg style="display: block; max-width:22px" viewBox="0 0 24 24">
						<path fill="currentColor" d="M15,5H17V3H15M15,21H17V19H15M11,5H13V3H11M19,5H21V3H19M19,9H21V7H19M19,21H21V19H19M19,13H21V11H19M19,17H21V15H19M3,5H5V3H3M3,9H5V7H3M3,13H5V11H3M3,17H5V15H3M3,21H5V19H3M11,21H13V19H11M7,21H9V19H7M7,5H9V3H7V5Z"></path>
					</svg>`,
				attributes: { title: 'View components' },
				})
				pn.addButton('options',btnPreview)
				pn.addButton('options',btnCode)
				pn.addButton('options',btnFullScreen)
				pn.addButton('options',newBtn4)
				pn.addButton('options',newBtn5)
				pn.addButton('options',newBtn6)
				
				//.set('label','Un test')

				this.myEditor.on('run:preview', () => { 
					//const self = this
					//console.log('Canvas :',self.myEditor.Components.getComponents())
					const modalContent = this.myEditor.editor;
					console.log('Canvas :',modalContent) //document.querySelector('.gjs-off-prv')
					console.log('Canvas :',modalContent.view.el.childNodes[3])
					//console.log('Canvas :',typeof(modalContent)) 
					//const btnprv = this.myEditor.getEl().querySelector('.gjs-off-prv')
					const spanElt = document.createElement('span')
					spanElt.className = 'gjs-off-prv'
					spanElt.setAttribute('style','display: inline-block;')
					const newElt = document.createElementNS('http://www.w3.org/2000/svg','svg')
					newElt.setAttribute('style','display: block; max-width:22px')
					newElt.setAttribute('viewBox','0 0 620 620')
					const pathObj = document.createElementNS('http://www.w3.org/2000/svg', 'path')
					pathObj.setAttribute('fill','currentColor')
					pathObj.setAttribute('d','M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z')
					//
					newElt.appendChild(pathObj)
					//spanElt.appendChild(newElt)
					//newElt.append(`<svg><path d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"/></svg>`)
					modalContent.view.el.childNodes[3].className = 'gjs-off-prv'
					modalContent.view.el.childNodes[3].setAttribute('style','display: inline-block;')
					console.log('Canvas :',modalContent.view.el.childNodes[3])
					if (modalContent.view.el.childNodes[3].childNodes.length>0) {
						modalContent.view.el.childNodes[3].childNodes[0].replaceWith(newElt)
					} else {
						modalContent.view.el.childNodes[3].appendChild(newElt)
					}
					//modalContent.view.el.childNodes[3].remove()
					//modalContent.view.el.childNodes[3].append(spanElt)
					
					
				});


				this.myEditor.onReady(() => {
					// perform actions
					this.myEditor.Components.getWrapper().set('content', '<mjml><mj-body></mj-body></mjml>')
				});
		},
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
			
			this.myEditor.Components.getWrapper().set('content', '<mjml><mj-body><mj-section></mj-section></mj-body></mjml>')
			this.myEditor.editor.html =  '<mjml><mj-body><mj-section></mj-section></mj-body></mjml>'
			console.log('My editor content :',this.myEditor)
			console.log('My editor mjml :',this.myEditor.getHtml())
			console.log('My editor css :',this.myEditor.getCss())
			console.log('My html :',this.myEditor.runCommand('mjml-code-to-html').html)
			/*this.$nextTick(() => {
				this.$refs.content.focus()
			})*/
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
				//this.showBuilder = true;
				this.initEditor()
				
				/*this.$nextTick(() => {
					this.$refs.title.focus()
				})*/
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

	#gjs {
		
		z-index: 2000;
	}
	#gjs >>> .gjs-mdl-content {
	text-align: left !important;	
	}
	#gjs >>> .gjs-pn-panels {
		text-align: left !important;
	}

	#gjs  >>> .gjs-pn-btn {
		color: white !important;
	}

	#gjs >>> .gjs-off-prv {
		background-color: var(--side-menu-background-color) !important;
		min-height: 30px;
    	min-width: 30px;
	}

	#gjs >>> .gjs-sector-label  {
		color: white !important;
	}

	#gjs >>> .gjs-two-color {
		color: white !important;
	}

	#gjs >>> .gjs-one-bg  {
		background-color: var(--side-menu-background-color) !important;
	}

	#gjs >>>  .gjs-layer-title {
		/*font-family: 'Font Awesome\ 5 Free';*/
		font-style: normal;
		font-weight: 900;
		padding: 3px 10px 18px 30px !important;
		display: flex;
		align-items: left;
	}

	#gjs >>> .gjs-editor-cont {
		height: 100% !important;
	}

	#gjs >>> .gjs-pn-views-container {
		width: 20% !important;
	}

	#gjs >>> .gjs-pn-commands {
		width: 80%;
	}

	#gjs >>> .gjs-pn-options {
		right: 20%;
	}

	#gjs >>> .gjs-pn-views {
		width: 20%;
	}

	#gjs >>> .gjs-mdl-container {
		font-family: Helvetica,sans-serif;
		overflow-y: auto;
		position: fixed;
		background-color: rgba(0,0,0,.5);
		display: flex;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 1002 !important;
	}

	#gjs >>> .gjs-cv-canvas {
		background-color: rgba(0,0,0,.15);
		box-sizing: border-box;
		width: 80%;
		height: calc(100% - 40px);
		bottom: 0;
		overflow: hidden;
		z-index: 1;
		position: absolute;
		left: 0;
		top: 40px;
	}


	#emptycontent {
		color: var(--color-text-maxcontrast);
		text-align: center !important;
		margin-top: 50px !important;
		width: 100% !important;
		height: 100% !important;
	}

	#emptycontentEditor {
		/*background-color: var(--color-text-maxcontrast) !important;
		color: var(--color-text-maxcontrast);*/
		text-align: center;
		margin-top: 50px !important;
		width: 100%;
		height: 100%;
	}

	#emptycontentEditor >>> .gjs-one-bg {
		background-color: var(--color-text-maxcontrast) !important;
	}

	#app-navigation-vue {
		z-index:1001 !important;
	}
	/*#app-content > div {
		width: 100%;
		height: 100%;
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}*/

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
