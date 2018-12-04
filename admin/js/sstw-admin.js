
(function ($) {
    'use strict';

    var sstwApp;

    function Sstw() {
        var self = this;

        this.staticItems = window.sstwData.staticItems;
        this.translations = window.sstwData.i18n;

        this.store = new Sstw.Store;
        this.results = new Sstw.ResultsList;

        this.results.on('reset', function() {
           this.selectedModelIndex = null;
           this.next();
        });

        this.fuse.setCollection(this.staticItems);

        this.nextTick(function() {
            this.appView = new Sstw.AppView;
            this.store.set('staticItems', this.staticItems);
        });

        Sstw.updateActiveItemBackgroundColor();
    }

    Sstw.$main = $('#wp-admin-bar-search').first();
    Sstw.$sepTemplate = $('#sstw-tep-template').first();
    Sstw.$itemTemplate = $('#sstw-item-template').first();
    Sstw.$searchResultsContainer = $('#sstw-search-results-container').first();
    Sstw.$searchResultsList = $('#sstw-search-results-list').first();

    Sstw.activeItemBackgroundColor = 'rgba(255,255,255,0.2)';

    Sstw.updateActiveItemBackgroundColor = function() {
        if(! Sstw.$main) {
            return;
        }

        var element = Sstw.$main.parents('#wpadminbar').find('.ab-sub-wrapper').first();

        if(! element) {
            element = Sstw.$main.parents('#wpadminbar').first();
        }

        var color = element.css('background-color');

        if(! color) {
            return;
        }

        var regex =  /^(?:rgba?)?[\s]?[\(]?[\s+]?(\d+)[(\s)|(,)]+[\s+]?(\d+)[(\s)|(,)]+[\s+]?(\d+)[(\s)|(,)]+[\s+]?([0-1]?(?:\.\d+)?)$/i;
        color = regex.exec(color);

        color = color.filter(function(e, i) {
            return !(i === 0 || !e);


        });

        Sstw.activeItemBackgroundColor = 'rgba('+invert.asRgbArray(color, true).join(',')+',0.2)';
    };

    Sstw.prototype.search = function() {
        var query = this.store.get('searchQuery');

        if(! query) {
            this.results.reset();

            return;
        }

        var r = this.fuse.search(query);

        r = r.map(function(item) {
            return $.extend(item.item, {
                score: r.score
            });
        });

        this.results.reset(r);

        if(query.length < 3) {
            return;
        }

        this.store.set('isLoading', true);

        var searchDb = _.throttle(function() {
            $.post(ajaxurl, {
                'action': 'sstw_search',
                'type': 'all',
                'q': query
            })
            .success(function(data) {
                data = JSON.parse(data);

                if(! data || ! data.success) {
                    this.store.set('isLoading', false);

                    return;
                }

                this.fuse.setCollection(_.union(this.staticItems, data.data));
                var r = this.fuse.search(query);

                r = r.map(function(item) {
                    return $.extend(item.item, {
                        score: r.score
                    });
                });

                r = _.sortBy(r, 'type');

                this.results.reset(r);

                this.store.set('isLoading', false);
            }.bind(this))
            .error(function(err) {
                this.store.set('isLoading', false);
                console.log(err);
            }.bind(this));
        }.bind(this), 50, {trailing: false});

        searchDb();
    };

    Sstw.prototype.nextTick = function(callback) {
        setTimeout(callback.bind(this), 0);
    };

    Sstw.prototype.fuse = new Fuse([], {
        shouldSort: true,
        tokenize: true,
        threshold: 0.1,
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

    Sstw.Store = Backbone.Model.extend({
        idAttribute: 'id',

        defaults: function() {
            return {
                'id': 'sstw_store',
                'isLoading': false,
                'isActive': false,
                'searchQuery': '',
                'staticItems': []
            };
        }
    });

    Sstw.Result = Backbone.Model.extend({
        idAttribute: 'id',

        defaults: function() {
            return {
                'id': '',
                'title': '',
                'url': '#',
                'score': 0,
                'aliases': [],
                'parentId': '',
                'type': '',
                'typeLabel': '',
                'short': '',
                'focus': false
            };
        }

    });

    Sstw.ResultsList = Backbone.Collection.extend({
        comparator: 'score',
        model: Sstw.Result,
        selectedModelIndex: null,

        focusModel: function(modelIndex) {
            this.models[modelIndex].set('focus', true);
        },

        unfocusModel: function(modelIndex) {
            this.models[modelIndex].set('focus', false);
        },

        getFocusedModel: function() {
            if(this.selectedModelIndex === null) {
                return false;
            }

            return this.models[this.selectedModelIndex];
        },

        next: function(e) {
            if(e && e.preventDefault) {
                e.preventDefault();
            }

            if(this.length <= 0) {
                return;
            }

            if(this.selectedModelIndex === null) {
                this.selectedModelIndex = 0;
                this.focusModel(0);

                return;
            }

            var nextIndex = this.selectedModelIndex + 1;


            if(nextIndex >= this.length) {
                nextIndex = 0;
            }

            if(this.models[this.selectedModelIndex]) {
                this.unfocusModel(this.selectedModelIndex);
            }

            this.selectedModelIndex = nextIndex;
            this.focusModel(nextIndex);
        },

        prev: function(e) {
            if(e && e.preventDefault) {
                e.preventDefault();
            }

            if(this.length <= 0) {
                return;
            }

            if(this.selectedModelIndex === null) {
                this.selectedModelIndex = 0;
                this.focusModel(0);

                return;
            }

            var nextIndex = this.selectedModelIndex - 1;

            if(nextIndex < 0) {
                nextIndex = this.length - 1;
            }

            if(this.models[this.selectedModelIndex]) {
                this.unfocusModel(this.selectedModelIndex);
            }

            this.selectedModelIndex = nextIndex;
            this.focusModel(nextIndex);
        }
    });

    Sstw.ResultView = Backbone.View.extend({
        tagName: 'li',
        className: 'sstw-search-results-list-item',
        template: _.template(Sstw.$itemTemplate.html()),

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render: function() {
            var attributes = this.model.toJSON();

            this.$el.html(this.template(attributes));

            if(this.model.get('focus')) {
                this.$el.css({'background-color': Sstw.activeItemBackgroundColor});

                if(this.$el[0].scrollIntoView) {
                    this.$el[0].scrollIntoView(false);
                }
            } else {
                this.$el.css({'background-color': ''});
            }

            return this;
        }
    });

    Sstw.ResultListGroupView = Backbone.View.extend({
        template: _.template(Sstw.$itemTemplate.html()),
    });

    Sstw.ResultListView = Backbone.View.extend({
        el: Sstw.$searchResultsList,

        sepTemplate: _.template(Sstw.$sepTemplate.html()),

        initialize: function() {
            var addAll = _.throttle(this.addAll.bind(this), 50);

            this.listenTo(sstwApp.results, 'add', addAll);
            this.listenTo(sstwApp.results, 'reset', addAll);
            this.listenTo(sstwApp.results, 'all', this.render);
        },

        render: function() {

            return this;
        },

        addOne: function(item) {
            var view = new Sstw.ResultView({model: item});

            this.$el.append(view.render().el);
        },

        addSep: function(item) {
            this.$el.append(this.sepTemplate(item));
        },

        addAll: function() {
            this.$el.html("");

            var groups = sstwApp.results.groupBy('type');
            var groupKeys = _.keys(groups);

            _.each(groupKeys, function (groupKey, groupIndex) {
                var items = groups[groupKey];

                if(items.length <= 0) {
                    return;
                }

                this.addSep({
                    label: items[0].get('typeLabel'),
                    key: groupKey,
                    count: items.length
                });

                _.each(items, function(item) {
                    this.addOne(item);
                }.bind(this));
            }.bind(this));
        }

    });

    Sstw.ResultsView = Backbone.View.extend({
        el: Sstw.$searchResultsContainer,

        initialize: function() {
            this.message = this.$('.sstw-search-results-message');
            this.listView = new Sstw.ResultListView;
            this.loader = this.$('.sstw-search-results-loader');

            this.listenTo(sstwApp.store, 'change', this.render);
            this.listenTo(sstwApp.results, 'all', this.render);

            $(window).resize(this.render.bind(this));
        },

        render: function() {
            this.$el.css({
                height: ($(window).height() - $('#wpadminbar').height()) + 'px'
            });

            if(! sstwApp.store.get('isActive')) {
                this.$el.removeClass('is-active');
                Sstw.$main.removeClass('hover');
            } else {
                this.$el.css({display: 'flex'})
                    .addClass('is-active');
                Sstw.$main.addClass('hover');
            }

            if(sstwApp.store.get('isLoading')) {
                this.loader.show();
            } else {
                this.loader.hide();
            }

            this.updateMessage();

            return this;
        },

        updateMessage: function () {
            if(! sstwApp.store.get('isActive')) {
                this.message.html("")
                    .hide();

                return;
            }

            if(! sstwApp.store.get('searchQuery')) {
                this.message.html(sstwApp.translations.startTyping)
                    .show();

                return;
            }

            if(sstwApp.results.length <= 0 && ! sstwApp.store.get('isLoading')) {
                this.message.html(sstwApp.translations.notFound)
                    .show();

                return;
            }

            this.message.hide();
        }

    });

    Sstw.AppView = Backbone.View.extend({
        el: Sstw.$main,

        events: {
            'keyup': ''
        },

        initialize: function() {
            this.input = this.$('#adminbar-search').first();
            this.form = this.$('#adminbarsearch').first();
            this.resultsView = new Sstw.ResultsView;

            this.$el.unbind();
            this.input.unbind();
            this.form.unbind();

            this.listenTo(sstwApp.store, 'change', this.render);

            this.input.on('input', this.searchOnInput.bind(this));

            this.input.on('focus', this.activate.bind(this));

            Mousetrap.bind(['f3'], this.activate.bind(this));

            Mousetrap(this.$el.find('div')[0]).bind(['esc'], this.deactivate.bind(this));

            Mousetrap(this.$el.find('div')[0]).bind(['up'], sstwApp.results.prev.bind(sstwApp.results));
            Mousetrap(this.$el.find('div')[0]).bind(['down'], sstwApp.results.next.bind(sstwApp.results));

            // Mousetrap(this.$el.find('div')[0]).bind(['esc'], this.deactivate.bind(this));

            Mousetrap(this.$el.find('div')[0]).bind(['enter'], this.onSubmit.bind(this));
            this.form.on('submit', this.onSubmit.bind(this));

            $(document).mouseup(function(e) {
                if (!this.$el.is(e.target) && this.$el.has(e.target).length === 0) {
                    this.deactivate();
                }
            }.bind(this));
        },

        render: function() {
            if(sstwApp.store.get('isActive')) {
                this.form.addClass('adminbar-focused');

                setTimeout(function() {
                    this.input.css('background-color', this.input.css('background-color'));
                    this.input.css('color', this.input.css('color'));
                }.bind(this), parseFloat(getComputedStyle(this.input.get(0))['transitionDuration']) * 1000);
            } else {
                this.form.removeClass('adminbar-focused');

                setTimeout(function() {
                    this.input.css('background-color', '');
                    this.input.css('color', '');
                }.bind(this), 0);
            }


            return this;
        },



        searchOnInput: function(e) {
            sstwApp.store.set('searchQuery', this.input.val());
            sstwApp.search();
        },

        activate: function (e) {
            if(e && e.preventDefault) {
                e.preventDefault();
            }

            if(! this.input.is(":focus")) {
                this.input.focus();
            }


            if(sstwApp.store.get('isActive')) {
                return;
            }

            setTimeout(function() {
                sstwApp.store.set('isActive', true);
                this.render();
            }.bind(this), 0);
        },

        deactivate: function (e) {
            if(! sstwApp.store.get('isActive')) {
                return;
            }

            this.input.val("");
            sstwApp.results.reset();

            if(e) {
                e.preventDefault();
            }

            sstwApp.store.set({
                'isActive': false,
                'isLoading': false,
                'searchQuery': ''
            });

            this.render();
        },

        onSubmit: function(e) {
            e.preventDefault();

            var $link = $('#' + sstwApp.results.getFocusedModel().get('id'));

            if(sstwApp.results.getFocusedModel() && $link.length) {
                window.location.href = $link[0].href;
            }

            return false;
        },

    });

    $(document).ready(function() {
        if(! Sstw.$main || ! window.sstwData) {
            return;
        }

        sstwApp = new Sstw();
    });

})(jQuery);
