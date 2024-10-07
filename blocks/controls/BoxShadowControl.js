import { __ } from '@wordpress/i18n';
import React from "react";
import { Dropdown, RangeControl, ColorIndicator, ColorPicker, Button } from '@wordpress/components';

const BoxShadowControl = ({ shadowAttribute, attributes, setAttributes }) => {
    const shadowString = attributes[shadowAttribute] || '';
    const [x, y, blur, spread, color] = shadowString.split(' ');

    const shadowValues = {
        x: x || '',
        y: y || '',
        blur: blur || '',
        spread: spread || '',
        color: color || '',
    };

    const handleBoxShadowChange = (key, value) => {
        const newShadow = { ...shadowValues, [key]: value };
        const shadowString = `${newShadow.x} ${newShadow.y} ${newShadow.blur} ${newShadow.spread} ${newShadow.color}`.trim();
        setAttributes({ [shadowAttribute]: shadowString });
    };

    const renderColorPicker = () => (
        <ColorPicker
            color={shadowValues.color || ''}
            onChangeComplete={(value) => handleBoxShadowChange('color', value.hex)}
            disableAlpha
        />
    );

    const getValue = (value = '') => (value ? parseInt(value) : null);

    return (
        <>
            <div className="lb-boxshadow-actions">
                <Button
                    className="latepoint-block-reset"
                    onClick={() => setAttributes({ [shadowAttribute]: "" })}
                    isSmall
                    disabled={!shadowString}
                    icon="dashicon dashicons dashicons-image-rotate"
                />
            </div>
            <RangeControl
                label={__('Horizontal')}
                value={getValue(shadowValues.x)}
                onChange={(value) => handleBoxShadowChange('x', `${value}px`)}
                min={-100}
                max={100}
            />
            <RangeControl
                label={__('Vertical')}
                value={getValue(shadowValues.y)}
                onChange={(value) => handleBoxShadowChange('y', `${value}px`)}
                min={-100}
                max={100}
            />
            <RangeControl
                label={__('Blur')}
                value={getValue(shadowValues.blur)}
                onChange={(value) => handleBoxShadowChange('blur', `${value}px`)}
                min={0}
                max={100}
            />
            <RangeControl
                label={__('Spread')}
                value={getValue(shadowValues.spread)}
                onChange={(value) => handleBoxShadowChange('spread', `${value}px`)}
                min={-100}
                max={100}
            />
            <Dropdown
                className="lb-color-settings-dropdown"
                renderToggle={({ isOpen, onToggle }) => (
                    <div className="lb-row lb-color-settings-w">
                        <div className="lb-label">{__('Color')}</div>
                        <Button onClick={onToggle} aria-expanded={isOpen}>
                            <ColorIndicator className="lb-color-indicator" colorValue={shadowValues.color || ''} />
                        </Button>
                    </div>
                )}
                renderContent={renderColorPicker}
            />
        </>
    );
};

export default BoxShadowControl;