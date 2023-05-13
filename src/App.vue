<template>
	<!--
    SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
    SPDX-License-Identifier: AGPL-3.0-or-later
    -->
	<NcContent  app-name="newsletterbuilder">
		<NcAppNavigation>
		  	<template #list>
			<NcAppNavigationNew v-if="!loading"
				:text="t('newsletterbuilder', 'Nouvelle Newsletter')"
				:disabled="false"
				button-id="new-newsletterbuilder-button"
				button-class="icon-add"
				@click="newNewsLetter" />
				
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
				
			</template>
			
		</NcAppNavigation>
		<NcAppContent>
			<!--<NcActions v-if="open"></NcActions>
			<ul >
			<li><NcActionButton v-if="!open" @click="toggleNavigation(open)" title="Toggle sidebar">
					<template #icon>
						<Plus :size="20" />
					</template>
				
				</NcActionButton>
			</li>
			
			</ul>
			-->	
			<div class="wrapper">
					<div class="status-header">
						<div class="location-wrapper"><h2>{{t('newsletterbuilder','Editeur de News Letters')}}</h2></div>
						<div class="action-wrapper" v-if="currentNewsLetter">
							<h2>Titre :</h2> <input  v-model="currentNewsLetter.title"/>
						</div>
						<div class="action-item" v-if="currentNewsLetter">
							
						</div>
						<div class="action-item">
							
							<NcButton  type="primary" @click="sendMailTest()">
								<template #icon >
									<Check :size="20" />
								</template>
								{{t('newsletterbuilder','Test e-mail')}}
							</NcButton>
						</div>
					</div>
				</div>
			
			<div v-show="currentNewsLetter" id="emptycontentEditor">
				
				<div id="gjs"  class="gjs-editor-cont" />
				
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
	</NcContent>
</template>

<script>

import '../css/grapes.min.css'
import '../css/fontawesome.min.css'
//import 'grapesjs/dist/css/grapes.min.css'
import 'grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css';
import grapesJS from 'grapesjs'
import fr from 'grapesjs/locale/fr'
import grapesJSMJML from 'grapesjs-mjml'
import grapesCE from 'grapesjs-component-code-editor';
import grapesCK from 'grapesjs-plugin-ckeditor';
import mjmlFR from 'grapesjs-mjml/locale/fr' 

import NcContent from '@nextcloud/vue/dist/Components/NcContent'
import NcActions from '@nextcloud/vue/dist/Components/NcActions'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'
import NcAppNavigationToggle from '@nextcloud/vue/dist/Components/NcAppNavigationToggle'

import Plus from 'vue-material-design-icons/Plus'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'
import Check from 'vue-material-design-icons/Check.vue'
import FileImportIcon from 'vue-material-design-icons/FileImport.vue'
import CogIcon from 'vue-material-design-icons/Cog.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import CodeJson from 'vue-material-design-icons/CodeJson.vue'

import { emit, subscribe, unsubscribe } from '@nextcloud/event-bus'

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
		NcButton,
		NcContent,
		NcActions,
		NcActionButton,
		NcAppContent,
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationNew,
		NcAppNavigationToggle,
		Plus,
		Delete,
		Check,
		CogIcon,
		Magnify,
		FileImportIcon,
		PlusIcon,
		FolderIcon,
		CodeJson,
		//mjml,
	},
	data() {
		return {
			// newsletters: [],
			open: false,
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
		subscribe('navigation-toggled', this.toggleNavigationByEventBus)
			
		try {
			await this.$store.dispatch('svrNewsletters')
		} catch (e) {
			console.error(e)
			showError(t('newsletterstutorial', 'Could not fetch newsletters'))
		}
		
		
		this.loading = false
	},
	unmounted() {
		
		unsubscribe('toggle-navigation', this.toggleNavigationByEventBus)
	},
	methods: {
		async sendMailTest() {
			//this.myEditor.runCommand('mjml-code-to-html').html
			this.currentNewsLetter.content = this.myEditor.getHtml()
			//this.myEditor.runCommand('mjml-code-to-html').html
			this.saveNewsLetter()
			await this.$store.dispatch('sendEmail',{sujet:`test de newsletter`, content:this.myEditor.runCommand('mjml-code-to-html').html, mailsto:'herve.dechavigny@cacem.fr,vevedh@gmail.com'});
		},
		toggleNavigation(state) {
			console.log('Open :',state)

			this.open = (typeof state === 'undefined') ? !this.open : !state
			
			const bodyStyles = getComputedStyle(document.body)
			const animationLength = parseInt(bodyStyles.getPropertyValue('--animation-quick')) || 100

			setTimeout(() => {
				emit('toggle-navigation', {
					open: this.open,
				})
				try {
					this.myEditor.refresh(true)
				} catch (e) {

				}
				
			// We wait for 1.5 times the animation length to give the animation time to really finish.
			}, 1.5 * animationLength)
		},
		toggleNavigationByEventBus({ open }) {
			console.log('Tobbgle navigation panel :',open)
			try {
					this.myEditor.refresh(true)
				} catch (e) {

				}
			
		},
		initEditor() {
				CKEDITOR.dtd.$editable.a = 1;
				this.myEditor = grapesJS.init({
						height:'100%',
						allowScripts: 1,
						avoidInlineStyle: false,
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
						plugins: [grapesJSMJML , grapesCE ],//grapesJSMJML
						pluginsOpts: {
						[grapesJSMJML]: {
							// Optional options
							i18n: { fr: mjmlFR },
							
						},
						/*[grapesCK]: {
							position: 'center',
							options: {
								startupFocus: true,
								extraAllowedContent: '*(*);*{*}', // Allows any class and any inline style
								allowedContent: true, // Disable auto-formatting, class removing, etc.
								enterMode: 1,
								extraPlugins: 'sharedspace,justify,colorbutton,panelbutton,font',
								toolbar: [
									{ name: 'styles', items: ['Font', 'FontSize' ] },
									['Bold', 'Italic', 'Underline', 'Strike'],
									{name: 'paragraph', items : [ 'NumberedList', 'BulletedList']},
									{name: 'links', items: ['Link', 'Unlink']},
									{name: 'colors', items: [ 'TextColor', 'BGColor' ]},
								],
							}
						},*/
						[grapesCE]:{}
					},
				});
				console.log('My editor components :',this.myEditor.Panels.getPanel('options').buttons)
				//console.log('My editor components :',this.myEditor.Panels.getButton('options','sw-visibility').set('label','Un test'))	
				const buttonsOrig = Object.assign([],this.myEditor.Panels.getPanel('options').buttons.models)
				const pn = this.myEditor.Panels
				/**********************************************
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
				console.log('Button Preview :',btnPreview)*/
				/**************************************************
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
				console.log('Button FullScreen :',btnFullScreen)*/
				/***********************************************
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
				console.log('Button Preview :',btnCode)*/
				/*****************************************
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
				}*/
				/*************************************************
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
				console.log('Button 5 :',newBtn5)*/
				/**********************************
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
*/

				const panelViews = pn.getPanel('views')

				panelViews.get('buttons').add([{
					attributes: {
						title: 'Open Code'
					},
					className: 'fa fa-file-code',
					command: 'open-code',
					togglable: false, //do not close when button is clicked again
					id: 'open-code'
				}]);
				
				//.set('label','Un test')

				/*this.myEditor.on('run:preview', () => { 
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
					
					
				});*/

				this.myEditor.on('style:sector:add', (sector) => { 
					console.log('Sector :',sector)
				 });
				this.myEditor.on('stop:mjml-import', async () => {
					/*console.log('Editor wrapper :',this.myEditor.Components.getWrapper())
					console.log('Editor Asset Manager :',this.myEditor.AssetManager.getAll())
					//console.log('Editor images :',this.myEditor.Components.getComponent('mjml').find({ type:'mj-image' }))
					console.log('Editor content :',this.myEditor.Components.getComponent('mjml').findType('mj-image'))
					const allImages = await this.myEditor.Components.getComponent('mjml').findType('mj-image')
					//this.myEditor.Components.getComponent('mjml').findType('mj-image')[0].setAttributes('src','http://localhost:8080/')
					//.getTypes().filter(o => o.id == 'mjml'))
					//).filter(o => o.id == 'mj-image')
					console.log('All images :',allImages)
					if (Array.isArray(allImages) && allImages.length>0 ) {
						console.log('Component 0 :',allImages[0])
						console.log('Change settings...',this.myEditor.DomComponents.componentsById[allImages[0].ccid].get('attributes'))
						console.log('Change settings url',this.myEditor.DomComponents.componentsById[allImages[0].ccid].get('attributes').src)
						const reader = new FileReader()
						reader.onload = async function() {
							this.myEditor.DomComponents.componentsById[allImages[0].ccid].set({ src:reader.result })
						}
						reader.readAsArrayBuffer(this.myEditor.DomComponents.componentsById[allImages[0].ccid].get('attributes').src)
						
						//this.myEditor.DomComponents.componentsById[allImages[0].ccid].set({ src:'http://localhost:8080/'})
						console.log('Component 0 :',allImages[0].view.el)
					}*/
				})

				this.myEditor.on('change:style',this.handleStyleChange)
				this.myEditor.on('change:attributes',this.handleAttributeChange)


				this.myEditor.onReady(() => {
					// perform actions
					console.log('Editor wrapper :',this.myEditor)
					const p = this.myEditor.Panels.getPanel( 'views-container' );
					p.set( 'resizable', true );

					//this.myEditor.Panels.render();
					
					
				});
		},
		handleAttributeChange(m, v, opts) {
			console.log('Component attribute  m :',m)
			console.log('Component attribute v :',v)
			console.log('Component attribute opts :',opts)
			//this.myEditor.setStyle(this.get('attributes'), opts);
		},

		handleStyleChange(m, v, opts) {
			console.log('Component style  m :',m)
			console.log('Component style v :',v)
			console.log('Component style opts :',opts)
			/*const style = this.getStyle();
			delete style.__p;
			this.myEditor.set('attributes', style, opts);*/
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
			if (!this.myEditor) {
				this.initEditor()
			}
			//this.myEditor.Components.getWrapper().set('content', '<mjml><mj-body><mj-section></mj-section></mj-body></mjml>')
			//this.myEditor.editor.html =  '<mjml><mj-body><mj-section></mj-section></mj-body></mjml>'
			console.log('My editor content :',this.myEditor.editor)
			console.log('My editor mjml :',this.myEditor.getHtml())
			console.log('My editor css :',this.myEditor.getCss())
			//console.log('My html :',this.myEditor.runCommand('mjml-code-to-html').html)
			this.myEditor.Components.getWrapper().set('content', '')
			//this.myEditor.editor.html = newsletter.content;
			this.myEditor.setComponents((String(newsletter.content).trim()==='')?'<mjml><mj-body></mj-body></mjml>':newsletter.content)
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
				 this.createNewsLetter(this.currentNewsLetter)
			} else {
				 this.updateNewsLetter(this.currentNewsLetter)
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
				if (!this.myEditor) {
					this.initEditor()
					this.myEditor.Components.getWrapper().set('content', '')
					this.myEditor.setComponents('<mjml><mj-body></mj-body></mjml>')
				} else {
					this.myEditor.Components.getWrapper().set('content', '')
					this.myEditor.setComponents('<mjml><mj-body></mj-body></mjml>')
				}
				
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
<style scoped >



#gjs >>> 
.gjs-one-bg {
  background-color: #444
}

#gjs >>> 
.gjs-one-color {
  color: #444
}
#gjs >>> 
.gjs-one-color-h:hover {
  color: #444
}
#gjs >>> 
.gjs-two-bg {
  background-color: #fff
}

#gjs >>> 
.gjs-two-color {
  color: #fff
}

#gjs >>> 
.gjs-two-color-h:hover {
  color: #fff
}

#gjs >>> 
.gjs-three-bg {
  background-color: #804f7b
}

#gjs >>> 
.gjs-three-color {
  color: #804f7b
}

#gjs >>> 
.gjs-three-color-h:hover {
  color: #804f7b
}

#gjs >>> 
.gjs-four-bg {
  background-color: #eca700
}

#gjs >>> 
.gjs-four-color {
  color: #eca700
}

#gjs >>> 
.gjs-four-color-h:hover {
  color: #eca700
}

#gjs >>> 
.gjs-danger-bg {
  background-color: #dd3636
}

#gjs >>> 
.gjs-danger-color {
  color: #dd3636
}

#gjs >>> 
.gjs-danger-color-h:hover {
  color: #dd3636
}

#gjs >>> 
.gjs-bg-main,
.gjs-sm-colorp-c,
.gjs-off-prv {
  background-color: #444
}

#gjs >>>
.gjs-input:focus,
.gjs-button:focus,
.gjs-btn-prim:focus,
.gjs-select:focus,
.gjs-select select:focus {
  outline: none
}

#gjs >>>
.gjs-field input,
.gjs-field select,
.gjs-field textarea {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  color: inherit;
  border: none;
  background-color: rgba(0, 0, 0, 0);
  box-sizing: border-box;
  width: 100%;
  position: relative;
  padding: 5px;
  z-index: 1
}

#gjs >>>
.gjs-field input:focus,
.gjs-field select:focus,
.gjs-field textarea:focus {
  outline: none
}

#gjs >>>
.gjs-field input[type=number] {
  -moz-appearance: textfield
}

#gjs >>>
.gjs-field input[type=number]::-webkit-outer-spin-button,
.gjs-field input[type=number]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0
}

#gjs >>>
.gjs-field-range {
  flex: 9 1 auto
}

#gjs >>>
.gjs-field-integer input {
  padding-right: 20px
}

#gjs >>> 
 .gjs-layer-title {
		
		font-style: normal;
		font-weight: 900;
		padding: 0px 10px 5px 30px !important;
		display: flex;
		align-items: left;

}

#gjs >>> 
.gjs-layer-name {
		padding: 5px 0;
		display: inline-block;
		box-sizing: content-box;
		overflow: hidden;
		white-space: nowrap;
		margin: 0 30px 0 5px;
		max-width: 170px;
		height: auto;
}
#gjs >>> 
.code-panel section .codepanel-label {
    margin-top: 5px;
    line-height: 45px;
    font-size: 13px;
    color: #fdfbfb;
    user-select: none;
    text-transform: uppercase;
}

#gjs >>> 
.code-panel section .codepanel-separator {
    display: flex;
    justify-content: space-between;
    padding-left: 0.6rem;
    padding-right: 0.6rem;
    padding-top: 0;
    margin-top: 20px;
}

#gjs >>> .gjs-off-prv {
	background-color: var(--color-primary-default);
		border: none;
    font-size: 18px;
    margin-right: 5px;
    border-radius: 2px;
		min-height: 30px;
    	min-width: 30px;
		padding: 4px;
}

#gjs >>> .gjs-pn-views-container {
  height: 100%;
  padding: 2px 0 0;
  right: 0;
  width: 25% !important;
  overflow: auto;
  box-shadow: 0 0 5px rgba(0, 0, 0, .2)
}

#gjs >>> .gjs-layer-vis {
    left: 0;
    top: 0;
    padding: 5px 10px 0px 5px;
    position: absolute;
    box-sizing: content-box;
    cursor: pointer;
    width: 12px;
    z-index: 1;
}

#gjs >>> .gjs-sm-clear {
    cursor: pointer;
    width: 14px;
    min-width: 14px;
    height: 14px;
    margin-left: 5px;
    margin-bottom: inherit;
}

#gjs >>> .gjs-sm-properties {
  font-size: .75rem;
  padding: 5px 5px;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  box-sizing: border-box;
  width: 100%
}

/*	#gjs {
		
		
	}

	#gjs >>> .gjs-editor {
		font-family: Helvetica,sans-serif !important;
    	font-size: .75rem !important;
	}

	#gjs >>> .gjs-sm-property .gjs-sm-btn {
		background-color: rgba(33,33,33,.2);
		border-radius: 2px;
		box-shadow: 1px 1px 0 rgba(5,5,5,.2), 1px 1px 0 rgba(43,43,43,.2) inset;
		padding: 5px;
		position: relative;
		text-align: center;
		height: auto;
		width: 100%;
		cursor: pointer;
		color:  var(--color-primary-text) !important;
		box-sizing: border-box;
		text-shadow: -1px -1px 0 rgba(0,0,0,.2);
		border: none;
		opacity: .85;
		filter: alpha(opacity=85);
	}
	#gjs >>> input {
		padding: 2px !important;
		color:  var(--color-primary-text) !important;
	}
	#gjs >>> .gjs-input-unit {
		margin-left: 5px;
    	text-align: end;
		color:  var(--color-primary-text) !important;

	}
	#gjs >>> .gjs-field-color input {
		padding-right: 22px;
		box-sizing: border-box;
		height: 20px;
		min-height: 20px;
	}
	#gjs >>> .gjs-field input, .gjs-field select, .gjs-field textarea {
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
		color:  var(--color-primary-text) !important;
		border: none;
		background-color: rgba(0,0,0,0);
		box-sizing: border-box;
		width: 100%;
		position: relative;
		padding: 5px;
		z-index: 1;
	}
	#gjs >>> button:not(.button-vue,[class^=vs__]) {
		border-radius: inherit;
	}
	#gjs >>> gjs-btn-prim {
		color: inherit;
		background-color: rgba(255,255,255,.1);
		border-radius: 2px;
		padding: 3px 6px;
		padding: 5px;
		cursor: pointer;
		border: none;
	}

	#gjs >>> button {
		appearance: auto;
		writing-mode: horizontal-tb !important;
		font-style: ;
		font-variant-ligatures: ;
		font-variant-caps: ;
		font-variant-numeric: ;
		font-variant-east-asian: ;
		font-variant-alternates: ;
		font-weight: ;
		font-stretch: ;
		font-size: ;
		font-family: ;
		font-optical-sizing: ;
		font-kerning: ;
		font-feature-settings: ;
		font-variation-settings: ;
		text-rendering: auto;
		letter-spacing: normal;
		word-spacing: normal;
		line-height: normal;
		text-transform: none;
		text-indent: 0px;
		text-shadow: none;
		display: inline-block;
		text-align: center;
		align-items: flex-start;
		cursor: default;
		box-sizing: border-box;
		background-color: buttonface;
		margin: 0em;
		padding: 1px 6px;
		border-width: 2px;
		border-style: outset;
		border-color: buttonborder;
		border-image: initial;
	}
	#gjs >>> .gjs-mdl-content {
	text-align: left !important;	
	}
	#gjs >>> .gjs-pn-panels {
		text-align: left !important;
	}

	#gjs  >>> .gjs-pn-btn {
		color:  var(--color-primary-text) !important;
	}

	#gjs >>> .gjs-off-prv {
		background-color: var(--color-primary) !important;
		min-height: 30px;
    	min-width: 30px;
	}

	#gjs >>> .gjs-sector-label  {
		color:  var(--color-primary-text) !important;
	}

	#gjs >>> .gjs-two-color {
		color:  var(--color-primary-text) !important;
	}

	#gjs >>> .gjs-one-bg  {
		background-color: var(--color-primary) !important;
	}

	#gjs >>>  .gjs-layer-title {
		
		font-style: normal;
		font-weight: 900;
		padding: 0px 10px 5px 30px !important;
		display: flex;
		align-items: left;

	}
	#gjs >>> .gjs-layer-name {
		padding: 5px 0;
		display: inline-block;
		box-sizing: content-box;
		overflow: hidden;
		white-space: nowrap;
		margin: 0 30px 0 5px;
		max-width: 170px;
		height: auto;
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

	

	#gjs >>> .gjs-sm-sector  {
		
		color: inherit;
	}

	#gjs >>> .gjs-sm-sector-title  {
		padding: 2px !important;
		color:  inherit;
	}

	#gjs >>> .gjs-sm-sector .gjs-sm-field.gjs-sm-composite {
		color:  var(--color-primary-text) !important;
	}
*/

	#emptycontent {
		position: relative;
		color: var(--color-text-maxcontrast);
		text-align: center !important;
		
		width: 100% !important;
		height: 100% !important;
	}

	#emptycontentEditor {
		/*background-color: var(--color-text-maxcontrast) !important;
		color: var(--color-text-maxcontrast);*/
		/*text-align: center;
		margin-top: 0px !important;*/
		width: 100%;
		height: 100%;
		z-index: 1801;

	}

	#emptycontentEditor >>> .gjs-one-bg {
		background-color: var(--color-primary-default) !important;
	}

	/*#app-navigation-vue {
		z-index:1001 !important;
	}*/
	/*.app-content  {
	    position: initial;
    z-index: 1009;
    flex-basis: 100vw;
    height: 100%;
    margin: 0 !important;
    background-color: var(--color-main-background);
    min-width: 0;
    --topbar-margin: 4px;
	}*/
	.wrapper {
		/* 44px is the height of nextcloud/vue button (not exposed as a variable :[ ) */
		--nc-button-size: 44px;

		--vertical-padding: 4px;

		/* Sticky is better than fixed because fixed takes the element out of flow,
		which breaks the height, putting elements underneath */
		position: sticky;

		/* This is competing with the recipe instructions which have z-index: 1 */
		z-index: 1802;

		/* The height of the nextcloud header */
		top: 0px;
		display: flex;
		width: 100%;
		/* Make sure the wrapper is always at least as tall as the tallest element
		* we expect (primary button) to prevent flickering when loading, etc. */
		min-height: calc(44px + 2 * var(--vertical-padding));
		flex-direction: row;

		padding: var(--vertical-padding) 1rem var(--vertical-padding)
			calc(44px + 2 * var(--vertical-padding));
		border-bottom: 1px solid var(--color-border);
		background-color: var(--color-main-background);
		gap: 8px;
	}

	.status-header {
		display: flex;
		/* Allow the title to shrink*/
		min-width: 0;
		flex-basis: 0;
		flex-direction: row;
		flex-grow: 1;
		flex-shrink: 1;
		align-items: flex-start;
		justify-content: space-around;
	}

	.mode-indicator {
		font-size: 0.9em;
		line-height: 0.9em;
	}

	.location-wrapper {
		width: 100%;
		padding-top: 5px;
	}

	.action-wrapper {
		display:flex;
		flex:1;
	
	}

	.location-wrapper:only-child {
		display: flex;
		flex: 1;
		flex-direction: row;
		justify-content: center;
	}

	
	.action-item {
		padding-left: 5px;
		display: flex;
		margin-left: 0px;
		gap: 5px;
		flex: 1;
		flex-direction: row;
	}


	/* The .status-header is justify-content: space-around. If there is no
	* .mode-indicator, this will put the .location at the top. Override to place in
	* the center */
	.status-header:deep(.location) {
		/* Don't let the location go wider than the space available */
		width: 100%;
		margin: 0;
		font-size: 1.2em;
		line-height: 1em;
		/* overflow-x: hidden breaks overflow-y: visible
		https://stackoverflow.com/questions/6421966/css-overflow-x-visible-and-overflow-y-hidden-causing-scrollbar-issue */
		overflow-x: clip;
		overflow-y: visible;
		/* Show ... when overflowing */
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	/*input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}*/
</style>
