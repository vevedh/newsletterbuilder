import Vue from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'

export class NewsLettersApi {

    url(url) {
		url = `/apps/newsletterbuilder${url}`
		return generateUrl(url)
	}

    getNewsLetters() {
        return axios.get(this.url('/newsletters'))
        .then(
            (response) => {
                return Promise.resolve(response.data)
            },
            (err) => {
                return Promise.reject(err)
            }
        )
        .catch((err) => {
            return Promise.reject(err)
        })
    }

    addNewsLetter(newsletter) {
		return axios.post(this.url('/newsletters'), newsletter)
			.then(
				(response) => {
					return Promise.resolve(response.data)
				},
				(err) => {
					return Promise.reject(err)
				}
			)
			.catch((err) => {
				return Promise.reject(err)
			})
	}

    deleteNewsLetter(newsletterId) {
		return axios.delete(this.url(`/newsletters/${newsletterId}`))
			.then(
				(response) => {
					return Promise.resolve(response.data)
				},
				(err) => {
					return Promise.reject(err)
				}
			)
			.catch((err) => {
				return Promise.reject(err)
			})
	}

	updateNewsLetter(newsletterId, value) {
		return axios.put(this.url(`/${newsletterId}`), value)
			.then(
				(response) => {
					return Promise.resolve(response.data)
				},
				(err) => {
					return Promise.reject(err)
				}
			)
			.catch((err) => {
				return Promise.reject(err)
			})
	}

	updateValue(newsletterId, key, value) {
		return axios.put(this.url(`/${newsletterId}`), { key, value: '' + value })
			.then(
				(response) => {
					return Promise.resolve(response.data)
				},
				(err) => {
					return Promise.reject(err)
				}
			)
			.catch((err) => {
				return Promise.reject(err)
			})
	}

	searchSharees(params) {
		return axios.get(generateOcsUrl('apps/files_sharing/api/v1/sharees'), { params })
	}


}