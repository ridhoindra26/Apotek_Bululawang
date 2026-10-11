function hidePageLoader() {
    const loader = document.getElementById('page-loader')

    if (!loader || loader.dataset.hidden === 'true') {
        return
    }

    loader.dataset.hidden = 'true'
    loader.classList.add('is-hidden')

    setTimeout(() => {
        loader.remove()
    }, 300)
}

window.addEventListener('load', () => {
    const isGoodsReceiptPage = document.querySelector('[data-goods-receipt-form]')

    if (!isGoodsReceiptPage) {
        setTimeout(hidePageLoader, 150)
    }
})

window.addEventListener('goods-receipt:page-ready', () => {
    setTimeout(hidePageLoader, 150)
})