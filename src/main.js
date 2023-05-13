/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateFilePath } from '@nextcloud/router'
import { getRequestToken } from '@nextcloud/auth'


import Vue from 'vue'
import App from './App'
import store from './store/store'


// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken())

Vue.prototype.$window = window
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA
Vue.prototype.OCP = OCP
Vue.prototype.CKEDITOR = window.CKEDITOR

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')



Vue.mixin({ methods: { t, n } })

export default new Vue({
	el: '#content',
	store,
	render: h => h(App),
})
