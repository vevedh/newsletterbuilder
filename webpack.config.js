// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
    ckeditor: path.join(__dirname, 'src/js/', 'ckeditor.js'),
	pluginCkeditor: path.join(__dirname, 'src/js/', 'grapesjs-plugin-ckeditor.min.js'),
    main: path.join(__dirname, 'src/','main.js')
}

module.exports = webpackConfig
