import React, { useRef } from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import ErrorMsg from '../error';
import styles from '../form-component.module.css';

const RadioItem = ({
    name,
    choice,
    onSelect
}) => {

    const inputRef = useRef(null);

    return (
        <div className={classnames(styles.radioItem, {
            [styles.radioActive]: inputRef.current ? inputRef.current.checked: false
        })}>
            <label>{choice.name}
                <input
                    name={name}
                    type="radio"
                    value={choice.value}
                    onChange={() => onSelect(choice.value)}
                    ref={inputRef}
                    />
            </label>
        </div>
    )
}

const FormRadio = ({
    choices,
    name,
    value,
    refs,
    error,
    info,
    theme,
    defaultValue,
    disabled,
    onChangeValue,
    ...props
}) => {

    const renderChoices = () => choices.map((choice, i) => (
        <RadioItem
            onSelect={onChangeValue}
            choice={choice}
            name={name}
            value={value}
            key={`choice_${i}`}/>
    ))

    return (
        <div className={styles.form_input}>
            <div className={classnames(styles.radioContainer, {
                    'is-invalid': error
                })}>
                {renderChoices()}
            </div>
            {info && <div className={styles.formInfo}>{info}</div>}
            <ErrorMsg error={error}/>
        </div>
    )
}

FormRadio.propTypes = {
    choices: PropTypes.array.isRequired,
    defaultValue: PropTypes.any,
    name: PropTypes.string.isRequired,
    value: PropTypes.any.isRequired,
    info: PropTypes.string,
    type: PropTypes.string,
    error: PropTypes.any,
    disabled: PropTypes.bool,
    theme: PropTypes.string,
}

FormRadio.defaultProps = {
    type: 'text',
    theme: 'default',
    refs: null,
    defaultValue: null
}

export default FormRadio;