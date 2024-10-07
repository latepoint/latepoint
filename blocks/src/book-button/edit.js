/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import {
    Panel,
    PanelBody,
    PanelRow,
    TextControl,
    SelectControl,
    ToggleControl,
    TabPanel,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption
} from '@wordpress/components';

import {useEffect, useState} from '@wordpress/element';


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

import React from "react";
import TypographyControl from "../../controls/TypographyControl";
import ColorSelectorControl from "../../controls/ColorSelectorControl";
import BorderControl from "../../controls/BorderControl";
import PaddingBoxControl from "../../controls/PaddingBoxControl";
import {SettingsIcon, StylesIcon} from "../../controls/LatepointIcons";

const LatepointBookButtonWrapper = styled.div`
`;

const LatepointBookButton = styled.div`
`;

const PanelRowBlock = styled(PanelRow)`
    display: block;
    margin-bottom: 20px;
`;

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

    useEffect(() => {
        if (!attributes.id) {
            setAttributes({ id: Math.random().toString(36).substr(2, 9) + '-' + Date.now() });
        }
    }, []);

    const [isHovered, setIsHovered] = useState(false);
    const [activeTab, setActiveTab] = useState('settings');

    const Separator = styled.div`
        height: 1px;
        background-color: #e0e0e0;
    `;

    const generateStyles = () => {
        let styles = {}
        if (attributes.is_inherit) return styles;

        if (attributes.font_weight) styles.fontWeight = attributes.font_weight
        if (attributes.font_size) styles.fontSize = attributes.font_size
        if (attributes.text_transform) styles.textTransform = attributes.text_transform
        if (attributes.font_family) styles.fontFamily = attributes.font_family
        if (attributes.line_height) styles.lineHeight = attributes.line_height
        if (attributes.letter_spacing) styles.letterSpacing = attributes.letter_spacing

        if (attributes.border_radius) styles.borderRadius = attributes.border_radius
        if (attributes.bg_color) styles.backgroundColor = isHovered && attributes.bg_color_hover ? attributes.bg_color_hover : attributes.bg_color;
        if (attributes.text_color) styles.color = isHovered && attributes.text_color_hover ? attributes.text_color_hover : attributes.text_color;
        if (attributes.border_color) styles.borderColor = isHovered && attributes.border_color_hover ? attributes.border_color_hover : attributes.border_color;
        if (attributes.border_style) styles.borderStyle = attributes.border_style;
        if (attributes.border_width) styles.borderWidth = attributes.border_width;
        if (attributes.padding) styles.padding = attributes.padding;
        return styles
    }

    const getGeneralTabs = () => {
        let tabs = [
            {name: 'settings', title: <div className="lb-tab-head"><SettingsIcon/>Settings</div>}
        ];
        if(!attributes.is_inherit){
            tabs.push({name: 'styles', title: <div className="lb-tab-head"><StylesIcon/>Style</div>});
        }
        return tabs;
    }

    return (
        <div {...blockProps}>

            <InspectorControls>
                <TabPanel
                    className="lb-general-tabs"
                    activeClass="active-tab"
                    tabs={ getGeneralTabs() }
                    onSelect={(tabName) => setActiveTab(tabName)}
                >
                    {(tab) => {
                        if (tab.name === 'settings') {
                            return (
                                <>
                                    <PanelBody title="Button Settings" initialOpen={true}>
                                        <TextControl
                                            label="Caption"
                                            value={attributes.caption || ''}
                                            onChange={(value) => setAttributes({caption: value})}
                                        />
                                        <ToggleControl
                                            label="Inherit From Theme"
                                            checked={attributes.is_inherit}
                                            onChange={(value) => setAttributes({is_inherit: value})}
                                        />
                                    </PanelBody>
                                    <PanelBody title="Booking Form Settings" initialOpen={false}>
                                        <PanelRow>
                                            <ToggleControl
                                                label="Hide Summary Panel"
                                                checked={attributes.hide_summary}
                                                onChange={(value) => setAttributes({hide_summary: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <ToggleControl
                                                label="Hide Side Panel"
                                                checked={attributes.hide_side_panel}
                                                onChange={(value) => setAttributes({hide_side_panel: value})}
                                            />
                                        </PanelRow>
                                    </PanelBody>

                                    <PanelBody title="Step Settings" initialOpen={false}>
                                        <PanelRowBlock>
                                            <SelectControl
                                                value={attributes.selected_agent}
                                                label={__('Preselected Agent', 'latepoint')}
                                                onChange={(value) => setAttributes({selected_agent: value})}
                                                options={latepoint_helper.selected_agents_options}
                                            />
                                        </PanelRowBlock>
                                        <PanelRowBlock>
                                            <SelectControl
                                                value={attributes.selected_service}
                                                label={__('Preselected Service', 'latepoint')}
                                                onChange={(value) => setAttributes({selected_service: value})}
                                                options={latepoint_helper.selected_services_options}
                                            />
                                        </PanelRowBlock>
                                        <PanelRowBlock>
                                            <SelectControl
                                                value={attributes.selected_service_category}
                                                label={__('Preselected Service Category', 'latepoint')}
                                                onChange={(value) => setAttributes({selected_service_category: value})}
                                                options={latepoint_helper.selected_service_categories_options}
                                            />
                                        </PanelRowBlock>
                                        <PanelRowBlock>
                                            <SelectControl
                                                value={attributes.selected_location}
                                                label={__('Preselected Location', 'latepoint')}
                                                onChange={(value) => setAttributes({selected_location: value})}
                                                options={latepoint_helper.selected_locations_options}
                                            />
                                        </PanelRowBlock>
                                        <PanelRow>
                                            <TextControl
                                                label={__('Preselected Booking Start Date', 'latepoint')}
                                                value={attributes.selected_start_date || ''}
                                                placeholder="YYYY-MM-DD"
                                                onChange={(value) => setAttributes({selected_start_date: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label={__('Preselected Booking Start Time', 'latepoint')}
                                                value={attributes.selected_start_time || ''}
                                                placeholder="Minutes"
                                                onChange={(value) => setAttributes({selected_start_time: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label={__('Preselected Duration', 'latepoint')}
                                                value={attributes.selected_duration || ''}
                                                placeholder="Minutes"
                                                onChange={(value) => setAttributes({selected_duration: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label={__('Preselected Total Attendees', 'latepoint')}
                                                value={attributes.selected_total_attendees || ''}
                                                placeholder="Number"
                                                onChange={(value) => setAttributes({selected_total_attendees: value})}
                                            />
                                        </PanelRow>
                                    </PanelBody>

                                    <PanelBody title="Other Settings" initialOpen={false}>
                                        <PanelRow>
                                            <TextControl
                                                label="Source ID"
                                                value={attributes.source_id || ''}
                                                onChange={(value) => setAttributes({source_id: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label="Calendar Start Date"
                                                value={attributes.calendar_start_date || ''}
                                                placeholder="YYYY-MM-DD"
                                                onChange={(value) => setAttributes({calendar_start_date: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label="Show Services"
                                                placeholder="Comma separated service IDs"
                                                value={attributes.show_services || ''}
                                                onChange={(value) => setAttributes({show_services: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label="Show Service Categories"
                                                placeholder="Comma separated category IDs"
                                                value={attributes.show_service_categories || ''}
                                                onChange={(value) => setAttributes({show_service_categories: value})}
                                            />
                                        </PanelRow>
                                        <PanelRow>
                                            <TextControl
                                                label="Show Agents"
                                                placeholder="Comma separated agent IDs"
                                                value={attributes.show_agents || ''}
                                                onChange={(value) => setAttributes({show_agents: value})}
                                            />
                                        </PanelRow>

                                        <PanelRow>
                                            <TextControl
                                                label="Show Locations"
                                                placeholder="Comma separated location IDs"
                                                value={attributes.show_locations || ''}
                                                onChange={(value) => setAttributes({show_locations: value})}
                                            />
                                        </PanelRow>
                                    </PanelBody>
                                </>
                            );
                        }
                        if (tab.name === 'styles') {
                            return (
                                <>
                                    <Panel>
                                        <PanelBody>
                                            <ToggleGroupControl
                                                className="lb-toggle-group"
                                                isBlock
                                                isDeselectable={true}
                                                value={attributes.align}
                                                label={__('Alignment', 'latepoint')}
                                                onChange={(value) => {
                                                    setAttributes({align: value})
                                                }}
                                            >
                                                <ToggleGroupControlOption label="Left" value="left"/>
                                                <ToggleGroupControlOption label="Center" value="center"/>
                                                <ToggleGroupControlOption label="Right" value="right"/>
                                                <ToggleGroupControlOption label="Justify" value="justify"/>
                                            </ToggleGroupControl>
                                        </PanelBody>
                                    </Panel>

                                    {!attributes.is_inherit && (
                                        <Panel>
                                            <PanelBody>
                                                <PaddingBoxControl
                                                    label={__('Padding', 'latepoint')}
                                                    paddingAttribute="padding"
                                                    attributes={attributes}
                                                    setAttributes={setAttributes}
                                                />
                                            </PanelBody>
                                            <PanelBody title="Color" initialOpen={false}>
                                                <TabPanel
                                                    className="lb-tabs"
                                                    activeClass="active-tab"
                                                    tabs={[
                                                        {name: 'tab-normal', title: 'Normal',},
                                                        {name: 'tab-hover', title: 'Hover',},
                                                    ]}
                                                >
                                                    {(tab) => {
                                                        if (tab.name === 'tab-normal') {
                                                            return (
                                                                <>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="bg_color"
                                                                            label={__('Background Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="text_color"
                                                                            label={__('Text Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                </>
                                                            );
                                                        }
                                                        if (tab.name === 'tab-hover') {
                                                            return (
                                                                <>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="bg_color_hover"
                                                                            label={__('Background Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="text_color_hover"
                                                                            label={__('Text Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                </>
                                                            );
                                                        }
                                                    }}
                                                </TabPanel>
                                            </PanelBody>

                                            <PanelBody title="Text" initialOpen={false}>
                                                <TypographyControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr="font_size"></TypographyControl>
                                            </PanelBody>
                                            <PanelBody title="Border" initialOpen={false}>
                                                <BorderControl attributes={attributes} setAttributes={setAttributes} borderRadiusAttr="border_radius"></BorderControl>
                                            </PanelBody>
                                        </Panel>
                                    )}
                                </>
                            );
                        }
                    }}
                </TabPanel>

                <Panel>
                    <Separator></Separator>
                </Panel>

            </InspectorControls>

            <LatepointBookButtonWrapper
                className={'latepoint-book-button-wrapper ' + 'wp-block-button ' + (attributes.align ? `latepoint-book-button-align-${attributes.align}` : '')}>
                <LatepointBookButton
                    onMouseEnter={() => setIsHovered(true)}
                    onMouseLeave={() => setIsHovered(false)}
                    style={generateStyles()}
                    className="wp-block-button__link latepoint-book-button">
                    {attributes.caption}
                </LatepointBookButton>
            </LatepointBookButtonWrapper>
        </div>
    );
}
