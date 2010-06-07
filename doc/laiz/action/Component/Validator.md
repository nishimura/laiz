laiz.action.Component\_Validator
================================

Validate request arguments by ini file.

Action Ini File
---------------

    [validator]
    ; required
    file = ValidatorIniFile.ini
    errorAction = ActionName
    ; option
    errorMessage    = "Incorrect data entered."
    errorMessageKey = error
    errorKeyPrefix  = error
    stop            = 0


Validator Ini File
------------------

    [arg1]
    required = "arg1 is required."
    digit    = "arg1 is not digit."
    ; if setting stop flag then stop validation
    stop     = 1
    [arg2]
    ; with arguments
    between(1,3)  = "arg2 is between 1 and 3"
    [arg3]
    ; with request value
    equal($arg4) = "arg3 not equal arg4"


