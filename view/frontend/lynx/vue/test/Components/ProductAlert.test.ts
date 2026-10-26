import { hydrate, mountNode } from '@oocs/module-lynx-theme/test'
import ProductAlert from 'OOCS_LynxProductAlert/Components/ProductAlert.vue'
import { describe, expect, it } from 'vitest'

const props = {
    url: '/productalert/add/price/product_id/14/uenc/aHR0cDovL2x5bngudGVzdC9qb3VzdC5odG1s/',
    label: 'Notify me when the price drops',
}

describe('ProductAlert', () => {
    it('links to the sign-up endpoint', () => {
        const link = mountNode(ProductAlert, { props }).find('a')

        expect(link.text()).toBe('Notify me when the price drops')
        expect(link.attributes('href')).toBe(props.url)
    })

    it('renders nothing when the alert does not apply', () => {
        expect(
            mountNode(ProductAlert, { props: { ...props, url: '' } })
                .find('a')
                .exists()
        ).toBe(false)
    })

    it('shows a placeholder link in the editor preview', () => {
        const link = mountNode(ProductAlert, { props: { ...props, url: '' }, page: { preview: true } }).find('a')

        expect(link.attributes('href')).toBe('#')
        expect(link.text()).toBe('Notify me when the price drops')
    })

    it('server-renders and hydrates the same markup', async () => {
        const { html, warnings } = await hydrate(ProductAlert, { props })

        expect(html).toContain('Notify me when the price drops')
        expect(warnings).toEqual([])
    })
})
