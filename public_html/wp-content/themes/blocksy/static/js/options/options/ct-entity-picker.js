import { createElement, useEffect, useState, useMemo } from '@wordpress/element'
import { __ } from 'ct-i18n'

import cachedFetch from 'ct-wordpress-helpers/cached-fetch'
import Select from './ct-select'

const withUniqueIDs = (data) =>
	data.filter(
		(value, index, self) =>
			self.findIndex((m) => m.id === value.id) === index
	)

const EntityIdPicker = ({
	value,
	option,
	option: {
		entity = 'posts',
		post_type = 'post',
		placeholder,
		additionOptions = [],
	},
	return_type = 'id',
	purpose,
	onChange,
}) => {
	const [allEntities, setAllEntities] = useState([])
	const requestBody = useMemo(() => {
		const requestBody = {}

		if (entity === 'posts') {
			requestBody.post_type = post_type
		}

		if (entity === 'taxonomies') {
			requestBody.post_type = post_type
		}

		return {
			...requestBody,
			...(value ? { alsoInclude: value } : {}),
		}
	}, [entity, post_type, value])

	const fetchPosts = (searchQuery = '') => {
		cachedFetch(
			`${wp.ajax.settings.url}?action=blocksy_get_all_entities`,
			{
				entity,

				...(searchQuery ? { search_query: searchQuery } : {}),
				...requestBody,
			},
			{
				// Abort intermediary requests.
				// TODO: maybe add a more specific name to the fetcherName to
				// avoid clashes with other instances of the same component.
				fetcherName: `entity-picker`,
			}
		)
			.then((r) => r.json())
			.then(({ data: { entities } }) => {
				setAllEntities(withUniqueIDs([...entities]))
			})
	}

	useEffect(() => {
		fetchPosts()
	}, [entity, post_type])

	return (
		<Select
			purpose={purpose}
			option={{
				appendToBody: true,
				defaultToFirstItem: false,
				searchPlaceholder: __(
					'Type to search by ID or title...',
					'blocksy-companion'
				),
				placeholder,
				choices: [
					...additionOptions,
					...allEntities.map((entity) => ({
						key: entity.id,
						value: entity.label,
						...(entity.group ? { group: entity.group } : {}),
					})),
				],
				search: true,
			}}
			value={value}
			onChange={(entity_id) => {
				if (return_type === 'entity') {
					onChange(
						allEntities.find(({ id }) => id === entity_id) ||
							entity_id
					)

					return
				}

				return onChange(entity_id)
			}}
			onInputValueChange={(value) => {
				if (
					allEntities.find(
						({ label, id }) => label === value || id === value
					)
				) {
					return
				}

				fetchPosts(value)
			}}
		/>
	)
}

export default EntityIdPicker
