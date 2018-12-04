var sstwApp;

(function ($) {
    'use strict';

    function Sstw() {
        var self = this;

        this.searchFormActive = false;
        this.searchLoading = false;

        this.staticItems = window.sstwData.staticItems;

        this.$menuTriggerContainer = $('.sstw-search-trigger-link').first();
        this.$menuTriggerLink = this.$menuTriggerContainer.find('a').first();
        this.$searchFormContainer = null;
        this.$searchForm = null;
        this.$searchInput = null;
        this.$searchResultsContainer = null;
        this.$searchResultsContainer = null;
        this.$searchResultsList = null;

        this._setup();

        this.Result = Backbone.Model.extend({
            idAttribute: 'id',

            defaults: function() {
                return {
                    'id': '',
                    'title': '',
                    'url': '#',
                    'aliases': [],
                    'parentId': '',
                    'type': '',
                    'short': ''
                };
            }
        });

        this.ResultsList = Backbone.Collection.extend({
            comparator: 'score',
            model: this.Result
        });

        this.Results = new this.ResultsList;

        this.ResultView = Backbone.View.extend({
            tagName: 'li',
            className: 'sstw-search-results-list-item ab-submenu',
            template: _.template($('#sstw-item-template').html()),

            initialize: function() {
                this.listenTo(this.model, 'change', this.render);
                this.listenTo(this.model, 'destroy', this.remove);
            },

            render: function() {
                this.$el.html(this.template(this.model.toJSON()));

                return this;
            }
        });

        this.ResultsView = Backbone.View.extend({
            el: this.$searchResultsList,

            initialize: function() {
                this.listenTo(self.Results, 'add', this.addOne);
                this.listenTo(self.Results, 'reset', this.addAll);
                this.listenTo(self.Results, 'all', this.render);
                console.log(this);
            },

            render: function() {


                return this;
            },

            addOne: function(item) {
                var view = new self.ResultView({model: item});

                this.$el.append(view.render().el);
            },

            addAll: function() {
                this.$el.html("");
                self.Results.each(this.addOne, this);
            }

        });

        this.resultsView = new this.ResultsView;

    }

    Sstw.prototype.fuse = new Fuse([], {
        shouldSort: true,
        tokenize: true,
        includeScore: true,
        includeMatches: true,
        keys: [
            {
                name: 'title',
                weight: 0.5
            },
            {
                name: 'short',
                weight: 1
            },
            {
                name: "aliases",
                weight: 0.3
            }
        ]
    });

    Sstw.prototype._setup = function() {
        this._setupTemplates();

        setTimeout(function() {
            this._setupEvents();
        }.bind(this), 0);
    };

    Sstw.prototype._setupTemplates = function() {
        this.$searchFormContainer = $('<div class="sstw-search-form-container" />');
        this.$searchForm = $('<form class="sstw-search-form wp-ui-primary wp-ui-text-highlight" />');
        this.$searchInput = $('<input type="search" class="sstw-search-input" />');
        this.$searchResultsContainer = $('<div class="ab-sub-wrapper sstw-search-results-container noticon" />');
        this.$searchResultsMessage = $('<span class="sstw-search-results-message" />');
        this.$searchResultsLoader = $('<p class="sstw-search-results-loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 30" fill="#fff"><circle cx="15" cy="15" r="15"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="15" repeatCount="indefinite" to="15" values="15;9;15"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="1" repeatCount="indefinite" to="1" values="1;.5;1"/></circle><circle cx="60" cy="15" r="9" fill-opacity="0.3"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="9" repeatCount="indefinite" to="9" values="9;15;9"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="0.5" repeatCount="indefinite" to="0.5" values=".5;1;.5"/></circle><circle cx="105" cy="15" r="15"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="15" repeatCount="indefinite" to="15" values="15;9;15"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="1" repeatCount="indefinite" to="1" values="1;.5;1"/></circle></svg></p>');
        this.$searchResultsList = $('<ul class="sstw-search-results-list ab-submenu" />');

        this.$searchForm.append(this.$searchInput);
        this.$searchFormContainer.append(this.$searchForm);

        this.$searchResultsContainer.append(this.$searchResultsMessage);
        this.$searchResultsContainer.append(this.$searchResultsList);
        this.$searchResultsContainer.append(this.$searchResultsLoader);

        this.$menuTriggerContainer.append([this.$searchFormContainer, this.$searchResultsContainer]);
    };

    Sstw.prototype._setupEvents = function() {
        this.$searchForm.on('submit', this.__preventDefault.bind(this));
        this.$menuTriggerContainer.unbind();
        this.$menuTriggerLink.unbind();

        this.$searchInput.on('input', this.search.bind(this));

        $('.scheme-list .color-option').click(function() {
            setTimeout(function() {
                this.updateStyle();
            }.bind(this), 50);
        }.bind(this));
    };

    Sstw.prototype.activateForm = function() {
        this.searchFormActive = true;
        this.$menuTriggerContainer.addClass('hover');
        this.updateStyle();

        this.$searchInput.val("").focus();

        this.message("Start typing to search");
        this.loading(false);

        this.fuse.setCollection(this.staticItems);
    };

    Sstw.prototype.search = function(e) {
        var query = e.target.value;

        if(! query) {
            this.message("Start typing to search");
        } else {
            this.message();
        }

        var r = this.fuse.search(query);

        r = r.map(function(item) {
            return $.extend(item.item, {
                score: r.score
            });
        });

        this.Results.reset(r);
    };

    Sstw.prototype.message = function(message) {
        if(! message) {
            this.$searchResultsMessage.html("")
                .removeClass('is-active');

            return;
        }

        this.$searchResultsMessage.html(message)
            .addClass('is-active');
    };

    Sstw.prototype.loading = function(status) {
        this.searchLoading = status;

        if(status) {
            this.$searchResultsLoader.addClass('is-active');
        } else {
            this.$searchResultsLoader.removeClass('is-active');
        }
    };

    Sstw.prototype.hideMessage = function() {
        this.$searchResultsMessage.html('')
            .removeClass('is-active');
    };

    Sstw.prototype.updateStyle = function() {
        this.$searchInput.css('border-color', this.$menuTriggerLink.css('color'));
        this.$searchForm.css('background-color', this.$menuTriggerLink.css('background-color'));
        this.$searchInput.css('color', this.$menuTriggerContainer.prev().find('a').css('color'));
    };

    Sstw.prototype.__preventDefault = function(e) {
        e.preventDefault();

        console.log('prevent');

        return false;
    };

    $(document).ready(function() {
        if($('#sstw-item-template').length <= 0) {
            return;
        }

        sstwApp = new Sstw();
        sstwApp.activateForm();
    });

})(jQuery);
