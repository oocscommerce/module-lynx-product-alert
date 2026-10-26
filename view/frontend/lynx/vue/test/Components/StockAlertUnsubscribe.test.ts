import { mountNode } from '@oocs/module-lynx-theme/test'
import StockAlertUnsubscribe from 'OOCS_LynxProductAlert/Components/StockAlertUnsubscribe.vue'
import { describe, expect, it, vi } from 'vitest'

const props = { action: '/productalert/unsubscribe/stock/', product_id: 14, labels: { loading: 'Loading...' } }

const stubSubmit = () => vi.spyOn(HTMLFormElement.prototype, 'submit').mockImplementation(() => {})

describe('StockAlertUnsubscribe', () => {
    it('posts itself with the product and a form key matching the cookie', () => {
        const submit = stubSubmit()
        const wrapper = mountNode(StockAlertUnsubscribe, { props })
        const form = wrapper.find('form').element as HTMLFormElement
        const data = new FormData(form)

        expect(submit).toHaveBeenCalledTimes(1)
        expect(form.getAttribute('action')).toBe('/productalert/unsubscribe/stock/')
        expect(form.getAttribute('method')).toBe('post')
        expect(data.get('product')).toBe('14')
        expect(data.get('form_key')).toMatch(/^[0-9a-zA-Z]{16}$/)
        expect(document.cookie).toContain(`form_key=${data.get('form_key')}`)
        expect(wrapper.text()).toBe('Loading...')
        // A mounted app would re-render into the next test's page and post again
        wrapper.unmount()
    })

    it('stays on the page in the editor preview', () => {
        const submit = stubSubmit()
        const wrapper = mountNode(StockAlertUnsubscribe, { props, page: { preview: true } })

        expect(submit).not.toHaveBeenCalled()
        wrapper.unmount()
    })
})
