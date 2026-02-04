import type { ComponentType } from 'react';
import ForgotPasswordForm from './ForgotPasswordForm';
import LoginForm from './LoginForm';
import LocaleSwitcher from './LocaleSwitcher';
import RegisterForm from './RegisterForm';
import ResetPasswordForm from './ResetPasswordForm';

const islands: Record<string, ComponentType<any>> = {
    ForgotPasswordForm,
    LoginForm,
    LocaleSwitcher,
    RegisterForm,
    ResetPasswordForm,
};

export default islands;
