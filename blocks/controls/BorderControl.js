import {__} from '@wordpress/i18n';
import {
    SelectControl,
    __experimentalUnitControl as UnitControl,
    PanelRow,
    TabPanel
} from '@wordpress/components';

import React from "react";
import ColorSelectorControl from "./ColorSelectorControl";

const BorderControl = ({attributes, setAttributes, borderRadiusAttr}) => {

    return (
        <>
            <SelectControl
                label={__('Style', 'latepoint')}
                value={attributes.border_style}
                className="latepoint-control-two-columns"
                options={[
                    {label: __('Default', 'latepoint'), value: 'default'},
                    {label: __('None', 'latepoint'), value: 'none'},
                    {label: __('Solid', 'latepoint'), value: 'solid'},
                    {label: __('Dotted', 'latepoint'), value: 'dotted'},
                    {label: __('Dashed', 'latepoint'), value: 'dashed'},
                ]}
                onChange={(border_style) => setAttributes({border_style})}
            />

                <UnitControl
                    label={__('Border Radius')}
                    className="latepoint-control-two-columns"
                    onChange={(value) => {
                        setAttributes({[borderRadiusAttr]: value})
                    }}
                    units={[
                        {value: 'px', label: 'px', default: 0},
                        {value: '%', label: '%', default: 10},
                        {value: 'em', label: 'em', default: 0},
                    ]}
                    value={attributes[borderRadiusAttr]}
                />

                <UnitControl
                    label={__('Border Width')}
                    className="latepoint-control-two-columns"
                    onChange={(value) => {
                        setAttributes({border_width: value})
                    }}
                    units={[
                        {value: 'px', label: 'px', default: 0},
                        {value: '%', label: '%', default: 10},
                        {value: 'em', label: 'em', default: 0},
                    ]}
                    value={attributes.border_width}
                />

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
                                    <ColorSelectorControl
                                        attributes={attributes}
                                        setAttributes={setAttributes}
                                        colorAttribute="border_color"
                                        label={__('Border Color', 'latepoint')}
                                    ></ColorSelectorControl>
                                </>
                            );
                        }
                        if (tab.name === 'tab-hover') {
                            return (
                                <>
                                    <ColorSelectorControl
                                        attributes={attributes}
                                        setAttributes={setAttributes}
                                        colorAttribute="border_color_hover"
                                        label={__('Border Color', 'latepoint')}
                                    ></ColorSelectorControl>
                                </>
                            );
                        }
                    }}
                </TabPanel>
        </>
    );
};

export default BorderControl;
