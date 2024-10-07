import {
    Button,
    RangeControl,
    ButtonGroup
} from '@wordpress/components';
import {useState} from '@wordpress/element';
import React from "react";

const LetterSpacingControl = ({attributes, setAttributes}) => {

    const unitOptions = ['px', 'em'];

    const getLetterSpacingUnit = () => {
        const letterSpacing = attributes.letter_spacing;
        if (letterSpacing) {
            const match = letterSpacing.match(/[a-zA-Z%]+$/);
            return match ? match[0] : 'px';
        }
        return 'px';
    };

    const [unit, setUnit] = useState(getLetterSpacingUnit());
    const getLetterSpacingValue = () => {
        if (attributes.letter_spacing) {
            return parseFloat(attributes.letter_spacing);
        }
        return "";
    };

    const handleLetterSpacingChange = (newSize) => {
        if (!newSize) {
            setAttributes({letter_spacing: ''});
            return;
        }
        setAttributes({letter_spacing: `${newSize}${unit}`});
    };

    const handleUnitChange = (newUnit) => {
        setUnit(newUnit);
        const currentSize = parseFloat(attributes.letter_spacing);
        setAttributes({letter_spacing: `${currentSize}${newUnit}`});
    };

    return (
        <div className="letter-spacing-control">
            <div className="latepoint-block-header">
                <label className="latepoint-control-label">Letter Spacing</label>

                <div className="latepoint-block-header-actions">

                    <Button className="latepoint-block-reset"
                            onClick={() => handleLetterSpacingChange("")}
                            isSmall
                            disabled={attributes.letter_spacing === ''}
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
                value={getLetterSpacingValue()}
                onChange={handleLetterSpacingChange}
                min="0"
                max="20"
                step="0.01"
            />
        </div>
    )
};

export default LetterSpacingControl;