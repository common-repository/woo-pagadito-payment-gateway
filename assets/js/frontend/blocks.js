const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;
const { createElement } = window.wp.element;

const pagaditoData = getSetting('pagadito_data', {});
const title = decodeEntities(pagaditoData.title) || __('Pagadito Payments', 'woo-gutenberg-products-block');
const description = decodeEntities(pagaditoData.description || '');
const placeOrderButtonLabel = decodeEntities(pagaditoData.place_order_button_label || __('Complete Purchase with Pagadito', 'woo-gutenberg-products-block'));

const pagaditoPaymentMethod = {
    name: 'pagadito',
    label: createElement('div', {}, title),
    content: createElement('div', {}, description),
    edit: createElement('div', {}, description),
    canMakePayment: () => true,
    ariaLabel: title,
    placeOrderButtonLabel: placeOrderButtonLabel,
    supports: { features: pagaditoData.supports },
};

registerPaymentMethod(pagaditoPaymentMethod);