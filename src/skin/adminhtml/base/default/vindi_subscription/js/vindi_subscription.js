VindiSubscriptionCreditCard = Class.create();
VindiSubscriptionCreditCard.prototype = {
    initialize: function (config) {
        var self = this;

        self.paymentChoiceSavedCc = $$(config.paymentChoiceSavedCcSelector).first();
        self.paymentChoiceNewCc = $$(config.paymentChoiceNewCcSelector).first();
        self.blockChoiceSavedCc = $$(config.blockChoiceSavedCcSelector).first();
        self.blockChoiceNewCc = $$(config.blockChoiceNewCcSelector).first();

        self.initObservers();
    },

    initObservers: function () {
        var self = this;

        self.paymentChoiceSavedCc.observe('change', self.onPaymentChoiceSaved.bind(self));
        self.paymentChoiceNewCc.observe('change', self.onPaymentChoiceNew.bind(self));
    },

    onPaymentChoiceSaved: function () {
        var self = this;

        self.togglePaymentChoices(self.blockChoiceSavedCc, self.blockChoiceNewCc);
    },

    onPaymentChoiceNew: function () {
        var self = this;

        self.togglePaymentChoices(self.blockChoiceNewCc, self.blockChoiceSavedCc);
    },

    /*
     ======================================================
     --------------------SHOW/HIDE functions---------------
     ======================================================
     */
    togglePaymentChoices: function (hide, show) {
        var self = this;

        self.hideContainer(hide);
        self.showContainer(show);
    },

    showContainer: function (container) {
        var self = this;

        container.setStyle({'display': ''});
        var newHeight = self.getElementHeight(container);
        this.applyEffect(container, newHeight, 0.5, function () {
            container.setStyle({'height': ''});
            var requiredElems = container.querySelectorAll('.not-required-entry');
            for (var i in requiredElems) {
                if (!requiredElems.hasOwnProperty(i)) continue;

                requiredElems[i].classList.remove('not-required-entry');
                requiredElems[i].classList.add('required-entry');
            }
        });
    },

    hideContainer: function (container) {
        var self = this;

        self.applyEffect(container, 0, 0.5, function () {
            container.setStyle({'display': 'none'});
            var requiredElems = container.querySelectorAll('.required-entry');
            for (var i in requiredElems) {
                if (!requiredElems.hasOwnProperty(i)) continue;

                requiredElems[i].classList.remove('required-entry');
                requiredElems[i].classList.add('not-required-entry');
            }
        });
    },

    applyEffect: function (element, newHeight, duration, afterFinish) {
        if (element.effect) {
            element.effect.cancel();
        }
        var afterFinishFn = afterFinish || Prototype.emptyFunction;
        element.effect = new Effect.Morph(element, {
            style: {
                'height': newHeight + 'px'
            },
            duration: duration,
            afterFinish: function () {
                delete element.effect;
                afterFinishFn();
            }
        });
    },

    getElementHeight: function (element) {
        element = $(element);
        var origDimensions = element.getDimensions();
        var origHeight = element.style.height;
        var origDisplay = element.style.display;
        var origVisibility = element.style.visibility;
        element.setStyle({
            'height': '',
            'display': '',
            'visibility': 'hidden'
        });
        var height = Math.max(element.getDimensions()['height'], origDimensions['height']);
        element.setStyle({
            'height': origHeight,
            'display': origDisplay,
            'visibility': origVisibility
        });
        return height;
    }
};
