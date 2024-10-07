import { __ } from '@wordpress/i18n';
import { __experimentalBoxControl as BoxControl } from '@wordpress/components';
import React from "react";

const PaddingBoxControl = ({ label, paddingAttribute, attributes, setAttributes }) => {
    const defaultPadding = {
        top: '',
        right: '',
        bottom: '',
        left: ''
    };

    const handleBoxControlChange = (newValues) => {
        setAttributes({ [paddingAttribute]: paddingToString(newValues) });
    };

    const paddingValues = () => {
        if (!attributes[paddingAttribute]) return defaultPadding;

        const values = attributes[paddingAttribute].split(' ');
        const positions = ['top', 'right', 'bottom', 'left'];

        return positions.reduce((acc, position, index) => {
            acc[position] = values[index] || '';
            return acc;
        }, {});
    };

    const paddingToString = (padding) => {
        if (!padding.top && !padding.right && !padding.bottom && !padding.left) {
            return '';
        }
        return [
            padding.top || '0',
            padding.right || '0',
            padding.bottom || '0',
            padding.left || '0',
        ].join(' ').trim();
    };

    return (
        <div className="lb-boxcontrol">
            <BoxControl
                label={__(label)}
                values={paddingValues()}
                onChange={handleBoxControlChange}
                resetValues={defaultPadding}
            />
        </div>
    );
};

export default PaddingBoxControl;