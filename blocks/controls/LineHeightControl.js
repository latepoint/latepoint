import {
    Button,
    RangeControl,
    ButtonGroup
} from '@wordpress/components';
import {useState} from '@wordpress/element';
import React from "react";

const LineHeightControl = ({attributes, setAttributes}) => {

    const unitOptions = ['px', 'em'];

    const getLineHeightUnit = () => {
        const lineHeight = attributes.line_height;
        if (lineHeight) {
            const match = lineHeight.match(/[a-zA-Z%]+$/);
            return match ? match[0] : 'px';
        }
        return 'px';
    };

    const [unit, setUnit] = useState(getLineHeightUnit());
    const getLineHeightValue = () => {
        if (attributes.line_height) {
            return parseFloat(attributes.line_height);
        }
        return "";
    };

    const handleLineHeightChange = (newSize) => {
        if (!newSize) {
            setAttributes({line_height: ''});
            return;
        }
        setAttributes({line_height: `${newSize}${unit}`});
    };

    const handleUnitChange = (newUnit) => {
        setUnit(newUnit);
        let newSize;
        const currentSize = parseFloat(attributes.line_height);
        if (unit === 'px' && newUnit !== 'px') {
            newSize = (currentSize / 16).toFixed(2);
        } else if (unit !== 'px' && newUnit === 'px') {
            newSize = Math.round(currentSize * 16);
        } else {
            newSize = currentSize;
        }
        setAttributes({line_height: `${newSize}${newUnit}`});
    };

    return (
        <div className="line-height-control">
            <div className="latepoint-block-header">
                <label className="latepoint-control-label">Line Height</label>

                <div className="latepoint-block-header-actions">

                    <Button className="latepoint-block-reset"
                            onClick={() => handleLineHeightChange("")}
                            isSmall
                            disabled={attributes.line_height === ''}
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
                value={getLineHeightValue()}
                onChange={handleLineHeightChange}
                min={unit === 'px' ? 5 : 0.3125}
                max={unit === 'px' ? 80 : 8}
                step={unit === 'px' ? 1 : 0.1}
            />
        </div>
    )
};

export default LineHeightControl;