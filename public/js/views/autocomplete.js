// --------------------------------------------------
// Used in generic autocompletes and designed to be
// extended by other views that need extended feature
// --------------------------------------------------
define(function (require) {
	
	// dependencies
	var $ = require('jquery'),
		_ = require('underscore'),
		Backbone = require('backbone');
	require('typeahead');
			
	// public view module
	var Autocomplete = Backbone.View.extend({
		
		// Initial state and inheritable vars
		found: false,
		data: {}, // Stores the key (label) - value (row data) pairs
		id: null,
		title: null,
		selection: null,  // The whole object (from the JSON server response) that is chosen
		route: null,
		throttle: 150,
		last_query: null,
		
		// Init
		initialize: function () {
			_.bindAll(this);

			// Get the path to the controller.  If this is not specified via a
			// data attribtue of "controller-route" then we attempt to infer it from
			// the current URL.
			this.route = this.$el.data('controller-route');
			if (!this.route) this.route = window.location.pathname;
			
			// Cache selectors
			this.$input = this.$('input[type="text"]');

			// Init Bloodhound instance that tells typeahead where to get data
			this.bloodhound = new Bloodhound({
				remote: {
					url: this.url(),
					rateLimitWait: this.throttle
				},
				datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.val); },
				queryTokenizer: Bloodhound.tokenizers.whitespace
			});
			this.bloodhound.initialize();

			// Initialize the Bootstrap typahead plugin, which generates the
			// autocomplete menu
			this.$input.typeahead({
				highlight: true,
				hint: false // I don't like the visual redundancy this introduces
			},{
				displayKey: 'title',
				source: this.bloodhound.ttAdapter()
			});

			// Listen for matching events
			this.$input.on('typeahead:opened', _.bind(function() {
				this.$input.off('input change', this.match);
				this.$input.on('typeahead:selected typeahead:autocompleted', this.match);
			}, this));
			this.$input.on('typeahead:closed', _.bind(function() {
				this.$input.off('typeahead:selected typeahead:autocompleted', this.match);
				this.$input.on('input change', this.match);
			}, this));
				
		},

		// Form the URL for the query
		url: function() {
			return this.route+'/autocomplete?query=%QUERY';
		},
		
		// Callback from after the user inputs anything in the textfield.  Basically,
		// we want to constantly check if what they've entered is valid rather than
		// rely on bootstrap to tell us.  Cause their events to fire with every change
		// the user makes.
		match: function(e, suggestion, dataset) {
			
			// A suggestion was found
			if (suggestion) {
				this.found = true;
				this.title = suggestion.title;
				this.id = suggestion.id;
				this.selection = suggestion;
				
			// The current input is different than the old one and there
			// was no suggestion, so wipe it.
			} else if (this.title != this.$input.val()) {
				this.found = false;
				this.title = this.selection = this.id = null;
			}

		},
		
		// Add a new item to the data array.  Title is the key to the collection
		// dicitonary. Model is an object like: {id, title, columns:{}}
		add:function(title, model) {
			this.data[title] = model;
		}
		
	});
	
	return Autocomplete;
});
