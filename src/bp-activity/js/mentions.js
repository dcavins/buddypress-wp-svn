/* global bp */

window.bp = window.bp || {};

( function( bp, $, undefined ) {
	var mentionsQueryCache = [],
		mentionsItem;

	bp.mentions       = bp.mentions || {};
	bp.mentions.users = window.bp.mentions.users || [];

	if ( typeof window.BP_Suggestions === 'object' ) {
		bp.mentions.users = window.BP_Suggestions.friends || bp.mentions.users;
	}

	/**
	 * Adds BuddyPress @mentions to form inputs.
	 *
	 * @param {array} defaultList If array, becomes the suggestions' default data source.
	 * @since 2.1.0
	 */
	$.fn.bp_mentions = function( defaultList ) {
		var debouncer = function(func, wait) {
			var timeout;
			return function() {
				var context = this;
				var args = arguments;

				var callFunction = function() {
				   func.apply(context, args)
				};

				clearTimeout(timeout);
				timeout = setTimeout(callFunction, wait);
			};
		};

		var remoteSearch = function( text, cb ) {
			/**
			* Immediately show the pre-created friends list, if it's populated,
			* and the user has hesitated after hitting @ (no search text provided).
			*/
			if ( text.length === 0 && $.isArray( defaultList ) && defaultList.length > 0 ) {
				cb(defaultList);
				return;
			}

			mentionsItem = mentionsQueryCache[ text ];
			if ( typeof mentionsItem === 'object' ) {
				cb( mentionsItem );
				return;
			}

			var params = { 'action': 'bp_get_suggestions', 'term': text, 'type': 'members' };

			// Add the group ID to the request if group ID data is attached to the input.
			if ( ".wp-editor-area" === $( this ).selector
				   && typeof document.activeElement !== 'undefined'
				   && typeof document.activeElement.dataset !== 'undefined'
				   && document.activeElement.dataset.suggestionsGroupId !== 'undefined'
				   && $.isNumeric( document.activeElement.dataset.suggestionsGroupId ) ) {
				params['group-id'] = parseInt( document.activeElement.dataset.suggestionsGroupId, 10 );
			}

			return $.getJSON( ajaxurl, params )
				/**
				 * Success callback for the @suggestions lookup.
				 *
				 * @param {string} query Partial @mention to search for.
				 * @param {function} render_view Render page callback function.
				 * @param {object} response Details of users matching the query.
				 * @since 2.1.0
				 */
				.done( function( response ) {
					if ( ! response.success ) {
						cb([]);
						return;
					}

					var data = $.map( response.data,
						/**
						 * Create a composite index to determine ordering of results;
						 * nicename matches will appear on top.
						 *
						 * @param {array} suggestion A suggestion's original data.
						 * @return {array} A suggestion's new data.
						 * @since 2.1.0
						 */
						function( suggestion ) {
							suggestion.search = suggestion.search || suggestion.ID + ' ' + suggestion.name;
							return suggestion;
						}
					);

					mentionsQueryCache[ text ] = data;
					cb(data);
				});
		};

		var tributeParams = {
			values: debouncer( function (text, cb) {
				remoteSearch(text, users => cb(users));
			}, 250),
			lookup: 'search',
			fillAttr: 'ID',
			menuItemTemplate: function (item) {
				return '<img src="' + item.original.image + '" alt="Profile picture of ' + item.original.name + '"> @' + item.string;
			},
		};

		var tribute = new Tribute( tributeParams );

		$( this ).each( function() {
			tribute.attach( document.getElementById( $( this ).attr( "id" ) ) );
		});
	};

	$( document ).ready( function() {
		// Activity/reply, post comments, dashboard post 'text' editor.
		$( '.bp-suggestions, #comments form textarea' ).bp_mentions( bp.mentions.users );
	});

})( bp, jQuery );
