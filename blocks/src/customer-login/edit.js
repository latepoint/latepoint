/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {TextControl, Panel, PanelBody, PanelRow} from '@wordpress/components';


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

    const LatepointFormWrapper = styled.div`
        box-shadow: 0px 2px 4px -1px rgba(0,0,0,0.1);
        border-radius: 4px;
        border: 1px solid #ddd;
        border-bottom-color: #bbb;
        background-color: #fff;
        padding: 20px;
        max-width: 300px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    `;

    const LatepointBlockCaption = styled.div`
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    `;

    const LatepointFormTitle = styled.div`
        padding: 10px;
        border-radius: 4px;
        background-color: #eee;
        margin-bottom: 5px;
        width: 40%;
    `;
    const LatepointFormLink = styled.div`
        height: 10px;
        border-radius: 4px;
        background-color: #eee;
        width: 30%;
    `;

    const LatepointFormInput = styled.div`
        padding: 15px;
        border-radius: 4px;
        background-color: #f8f8f8;
    `;

    const LatepointFormFooter = styled.div`
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        padding-top: 10px;
    `;

    const LatepointButtonPrev = styled.div`
      padding: 15px;
      width: 30%;  
      background-color: #b4c6f5;
      border-radius: 4px;
    `;

  return (
      <div {...blockProps}>
        <InspectorControls>
          <Panel>
            <PanelBody title="Login Form Settings">
              <TextControl
                  label="Caption"
                  value={attributes.caption || ''}
                  onChange={(value) => setAttributes({caption: value})}
              />
            </PanelBody>
          </Panel>
        </InspectorControls>
          <div>
          <LatepointBlockCaption>{attributes.caption}</LatepointBlockCaption>
            <LatepointFormWrapper>
                <LatepointFormTitle></LatepointFormTitle>

                <LatepointFormInput></LatepointFormInput>
                <LatepointFormInput></LatepointFormInput>

                <LatepointFormFooter>
                    <LatepointButtonPrev></LatepointButtonPrev>
                    <LatepointFormLink></LatepointFormLink>
                </LatepointFormFooter>
            </LatepointFormWrapper>
          </div>
      </div>
  );
}
