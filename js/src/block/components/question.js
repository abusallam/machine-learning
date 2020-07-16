/**
 * External dependencies
 */
import classNames from 'classnames';
import memoize from 'memize';
import * as React from 'react';
import '@tensorflow/tfjs';
import * as qna from '@tensorflow-models/qna';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { BLOCK_CLASS } from '../constants';

/**
 * @typedef {Object} QuestionProps The Edit component props.
 * @property {string} category The category ID.
 * @property {string} className The class name.
 * @property {number} postId The current post ID.
 * @property {string} textSource The text source.
 */

/**
 * The Edit component for the block.
 *
 * @param {QuestionProps} props The component props.
 * @return {React.ReactElement} The question component.
 */
const Question = ( { category, className, postId, textSource } ) => {
	const [ question, setQuestion ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ answer, setAnswer ] = useState( '' );

	/**
	 * Gets the text to search for the answer to the question.
	 *
	 * @return {Promise<string>} The text to search for the answer to the question.
	 */
	const getTextToSearch = memoize( async () => {
		if ( 'category' === textSource && !! category ) {
			const result = await apiFetch( {
				path: addQueryArgs(
					'/wp/v2/posts/',
					{ categories: category }
				),
			} );

			if ( ! Array.isArray( result ) ) {
				return '';
			}

			return result.reduce( ( accumulator, currentValue ) => {
				if ( currentValue.content && currentValue.content.rendered ) {
					return accumulator + currentValue.content.rendered;
				}
				return accumulator;
			}, '' );
		}

		const result = await apiFetch( {
			path: `/wp/v2/posts/${ postId }`,
		} );

		// @ts-ignore
		return result.content && result.content.rendered ? result.content.rendered : '';
	} );

	/**
	 * Gets the TensorFlow model.
	 *
	 * @return Promise<qna.QuestionAndAnswer> The question and answer model.
	 */
	const getModel = memoize( async () => {
		return await qna.load();
	} );

	/**
	 * Submits the question and sets the answer.
	 */
	const submitQuestion = async () => {
		setIsLoading( true );
		const textToSearch = await getTextToSearch();

		if ( textToSearch ) {
			const model = await getModel();
			const newAnswers = await model.findAnswers( question, textToSearch );
			if ( newAnswers[ 0 ] && newAnswers[ 0 ].text ) {
				setAnswer( newAnswers[ 0 ].text );
			} else {
				setAnswer( __( 'Sorry, no answer found', 'machine-learning' ) );
			}
		}

		setIsLoading( false );
	};

	return (
		<div className={ classNames( BLOCK_CLASS, className ) }>
			<h4>{ __( 'Ask a question', 'machine-learning' ) }</h4>
			<input
				type="text"
				value={ question }
				onChange={ ( event ) => {
					setQuestion( event.target.value );
				} }
			/>
			<button
				disabled={ isLoading }
				onClick={ submitQuestion }
			>
				{ __( 'Get the answer', 'machine-learning' ) }
			</button>
			{ isLoading && <Spinner /> }
			{ !! answer && ! isLoading && (
				<>
					<h4>{ __( 'Answer', 'machine-learning' ) }</h4>
					<p>{ answer }</p>
				</>
			) }
		</div>
	);
};

export default Question;