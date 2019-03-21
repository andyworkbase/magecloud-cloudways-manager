/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */

define([
    'jquery',
    'underscore',
    'MageCloud_CloudwaysManager/js/action/manager',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function (
    $,
    _,
    managerAction,
    alert,
    confirm,
    $t
) {
    'use strict';

    $.widget('custom.cloudwaysManager', {
        options: {
            url: {},
            formKey: '',
            triggers: {
                state: 'cloudways_manager-service-state',
                enable: 'cloudways_manager-varnish-enable',
                disable: 'cloudways_manager-varnish-disable',
                purge: 'cloudways_manager-varnish-purge'
            },
            params: {
                'isAjax': true
            }
        },

        /**
         * Initializes
         *
         * @returns {exports}
         */
        initialize: function () {
            var self = this;

            this._super();
            managerAction.registerCallback(function (data) {

            });

            return this;
        },

        /**
         * @private
         */
        _create: function () {
            $('#' + this.options.triggers.state)
                .on('click', $.proxy(this._checkState, this));
            $('#' + this.options.triggers.enable)
                .on('click', $.proxy(this._enableService, this));
            $('#' + this.options.triggers.disable)
                .on('click', $.proxy(this._disableService, this));
            $('#' + this.options.triggers.purge)
                .on('click', $.proxy(this._purgeServiceCache, this));
        },

        /**
         * @param event
         */
        _checkState: function (event) {
            var self = this,
                url = self.options.url.serviceState,
                target = $(event.currentTarget),
                params = {
                    'form_key': this.formKey
                };

            $.extend(params, this.options.params);
            managerAction(url, 'POST', params);

            return false;
        },

        /**
         * @param event
         * @returns {boolean}
         * @private
         */
        _enableService: function (event) {
            var self = this,
                url = self.options.url.varnishEnable,
                target = $(event.currentTarget),
                params = {
                    'form_key': this.formKey
                };

            confirm({
                title: $.mage.__('Confirmation'),
                content: $.mage.__('Are you sure do you want to enable Varnish service?'),
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        $.extend(params, self.options.params);
                        managerAction(url, 'POST', params);
                    },

                    /** @inheritdoc */
                    always: function (e) {
                        e.stopImmediatePropagation();
                    }
                }
            });

            return false;
        },

        /**
         * @param event
         * @returns {boolean}
         * @private
         */
        _disableService: function (event) {
            var self = this,
                url = self.options.url.varnishDisable,
                target = $(event.currentTarget),
                params = {
                    'form_key': this.formKey
                };

            confirm({
                title: $.mage.__('Confirmation'),
                content: $.mage.__('Are you sure do you want to disable Varnish service?'),
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        $.extend(params, self.options.params);
                        managerAction(url, 'POST', params);
                    },

                    /** @inheritdoc */
                    always: function (e) {
                        e.stopImmediatePropagation();
                    }
                }
            });

            return false;
        },

        /**
         * @param event
         * @returns {boolean}
         * @private
         */
        _purgeServiceCache: function (event) {
            var self = this,
                url = self.options.url.varnishPurge,
                configurationUrl = self.options.url.configuration,
                target = $(event.currentTarget),
                params = {},
                note = '<strong>NOTE: </strong>' + 'You can <a href="' + configurationUrl +'">configure</a> manager ' +
                    'to purge Varnish cache automatically after Flush Cache Storage in Magento.';

            confirm({
                title: $.mage.__('Confirmation'),
                content: $.mage.__('Are you sure do you want to purge Varnish cache?' + '<br><br>' + note),
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        $.extend(params, self.options.params);
                        managerAction(url, 'POST', params);
                    },

                    /** @inheritdoc */
                    always: function (e) {
                        e.stopImmediatePropagation();
                    }
                }
            });

            return false;
        }
    });

    return $.custom.cloudwaysManager;
});
