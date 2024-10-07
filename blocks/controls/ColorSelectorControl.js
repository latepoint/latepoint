import {__} from '@wordpress/i18n';
import {Dropdown, Button, ColorIndicator, ColorPalette} from '@wordpress/components';
import {select} from '@wordpress/data';
import React, {useMemo} from "react";

const ColorSelectorControl = ({label, colorAttribute, attributes, setAttributes}) => {
    const colors = useMemo(() => select('core/block-editor').getSettings().colors, []);
    const renderColorPicker = () => (
        <ColorPalette
            value={attributes[colorAttribute]}
            colors={colors}
            enableAlpha
            onChange={(color) => setAttributes({[colorAttribute]: color})}
        />
    );

    return (
        <>
            <Dropdown
                className="lb-color-settings-dropdown"
                renderToggle={({isOpen, onToggle}) => (
                    <div className="lb-row lb-color-settings-w">
                        <div className="lb-label">{__(label)}</div>
                        <Button onClick={onToggle} aria-expanded={isOpen}>
                            <ColorIndicator className="lb-color-indicator" colorValue={attributes[colorAttribute]}/>
                        </Button>
                    </div>
                )}
                renderContent={renderColorPicker}
            />
        </>
    );
};

export default ColorSelectorControl;