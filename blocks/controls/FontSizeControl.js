import {
    Button,
    RangeControl,
    ButtonGroup
} from '@wordpress/components';
import {useState} from '@wordpress/element';
import React from "react";

const FontSizeControl = ({attributes, setAttributes, fontSizeAttr}) => {

    const unitOptions = ['px', 'rem', 'em'];

    const getFontSizeUnit = () => {
        const fontSize = attributes[fontSizeAttr];
        if (fontSize) {
            const match = fontSize.match(/[a-zA-Z%]+$/);
            return match ? match[0] : 'px';
        }
        return 'px';
    };

    const [unit, setUnit] = useState(getFontSizeUnit());
    const getFontSizeValue = () => {
        if (attributes[fontSizeAttr]) {
            return parseFloat(attributes[fontSizeAttr]);
        }
        return "";
    };

    const handleFontSizeChange = (newSize) => {
        if (!newSize) {
            setAttributes({[fontSizeAttr]: ''});
            return;
        }
        setAttributes({[fontSizeAttr]: `${newSize}${unit}`});
    };

    const handleUnitChange = (newUnit) => {
        setUnit(newUnit);
        let newSize;
        const currentSize = parseFloat(attributes[fontSizeAttr]);
        if (unit === 'px' && newUnit !== 'px') {
            newSize = (currentSize / 16).toFixed(2);
        } else if (unit !== 'px' && newUnit === 'px') {
            newSize = Math.round(currentSize * 16);
        } else {
            newSize = currentSize;
        }
        setAttributes({[fontSizeAttr]: `${newSize}${newUnit}`});
    };

    return (
        <div className="font-size-control">
            <div className="latepoint-block-header">
                <label className="latepoint-control-label">Font Size</label>

                <div className="latepoint-block-header-actions">

                    <Button className="latepoint-block-reset"
                            onClick={() => handleFontSizeChange("")}
                            isSmall
                            disabled={attributes[fontSizeAttr] === ''}
                            icon="dashicon dashicons dashicons-image-rotate"
                    />

                    <ButtonGroup className="latepoint-unit-selector">
                        {unitOptions.map((option) => (
                            <Button key={option} isPrimary={unit === option} onClick={() => handleUnitChange(option)}>
                                {option}
                            </Button>
                        ))}
                    </ButtonGroup>
                </div>
            </div>
            <RangeControl
                value={getFontSizeValue()}
                onChange={handleFontSizeChange}
                min={unit === 'px' ? 5 : 0.3125}
                max={unit === 'px' ? 80 : 8}
                step={unit === 'px' ? 1 : 0.1}
            />
        </div>
    );
};

export default FontSizeControl;