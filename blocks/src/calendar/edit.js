/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {TextControl, SelectControl, Panel, PanelBody, PanelRow} from '@wordpress/components';
import {__} from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import styled from "@emotion/styled";


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
    const blockProps = useBlockProps();

    const range = (start, end) => {
        return Array.from({ length: end - start + 1 }, (_, index) => start + index);
    }

    const LatepointCalendarWrapper = styled.div`
        box-shadow: 0px 2px 4px -1px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        border: 1px solid #ddd;
        border-bottom-color: #bbb;
        background-color: #fff;
        padding: 20px;
        max-width: 400px;
    `;

    const rangeTypes = [
        {label: 'Month', value: 'month'},
        {label: 'Week', value: 'week'},
    ];

    const LatepointCalendar = styled.div`
        border: 1px solid #ddd;
        border-right: none;
        background: #fbfbfb;
    `;

    const LatepointBlockCaption = styled.div`
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    `;

    const LCRow = styled.div`
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    `;

    const LCHead = styled.div`
        border-bottom: 1px solid #ddd;
        background: #f3f3f3;
    `;

    const LCCell = styled.div`
        padding: 10px;
        text-align: center;
        border-right: 1px solid #ddd;
    `;
    const LCDate = styled.div`
        padding: 5px;
        border-radius: 4px;
        background-color: #e1e1e1;
    `;

    const LCText = styled.div`
        padding: 3px;
        border-radius: 4px;
        background-color: #ecebeb;
    `;

    const weekCells = (type) => {
        return (
            <>
                {range(1, 7).map(() => (
                    <LCCell>{type === 'date' ? <LCDate></LCDate> : <LCText></LCText>}</LCCell>
                ))}
            </>
        )
    };


    return (
        <div {...blockProps}>
            <InspectorControls>
                <Panel>
                    <PanelBody title="Latepoint Calendar Settings">
                        <TextControl
                            label="Caption"
                            value={attributes.caption || ''}
                            onChange={(value) => setAttributes({caption: value})}
                        />
                        <TextControl
                            label={__('Date', 'latepoint')}
                            value={attributes.date || ''}
                            placeholder="YYYY-MM-DD"
                            onChange={(value) => setAttributes({date: value})}
                        />
                        <TextControl
                            label="Show Agents"
                            placeholder="Comma separated agent IDs"
                            value={attributes.show_agents || ''}
                            onChange={(value) => setAttributes({show_agents: value})}
                        />
                        <TextControl
                            label="Show Services"
                            placeholder="Comma separated service IDs"
                            value={attributes.show_services || ''}
                            onChange={(value) => setAttributes({show_services: value})}
                        />
                        <TextControl
                            label="Show Locations"
                            placeholder="Comma separated location IDs"
                            value={attributes.show_locations || ''}
                            onChange={(value) => setAttributes({show_locations: value})}
                        />

                        <SelectControl
                            label={__('View', 'latepoint')}
                            value={attributes.view || 'month'}
                            onChange={(value) => setAttributes({view: value})}
                            options={rangeTypes}
                        />
                    </PanelBody>
                </Panel>
            </InspectorControls>
            <LatepointCalendarWrapper>
                <LatepointBlockCaption>{attributes.caption}</LatepointBlockCaption>
                <LatepointCalendar>
                    <LCHead>
                        <LCRow>
                            { weekCells('date') }
                        </LCRow>
                    </LCHead>

                    { range(1, 4).map(() => (
                        <LCRow>
                            { weekCells('text') }
                        </LCRow>
                    ))}

                </LatepointCalendar>

            </LatepointCalendarWrapper>

        </div>
    );
}
