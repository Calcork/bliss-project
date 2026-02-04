interface InputConstraints {
    pattern?: string | null;
    minlength?: number | null;
    maxlength?: number | null;
}

interface LoginFormProps {
    action: string;
    errors?: string[];
    old_email?: string;
    t: {
        title: string;
        email: string;
        password: string;
        submit: string;
    };
    constraints: {
        email: InputConstraints;
        password: InputConstraints;
    };
    register_url: string;
    home_url: string;
    forgot_password_url: string;
    t_register: string;
    t_home: string;
    t_forgot_password: string;
    success_message?: string | null;
}

export default function LoginForm({ action, errors = [], old_email = '', t, constraints, register_url, home_url, forgot_password_url, t_register, t_home, t_forgot_password, success_message = null }: LoginFormProps) {
    return (
        <form method="post" action={action} className="login-form">
            <h1 className="login-form__title">{t.title}</h1>

            {success_message && (
                <div className="login-form__success">
                    <p>{success_message}</p>
                </div>
            )}

            {errors.length > 0 && (
                <div className="login-form__errors">
                    {errors.map((error, i) => (
                        <p key={i}>{error}</p>
                    ))}
                </div>
            )}

            <label className="login-form__label" htmlFor="email">{t.email}</label>
            <input
                className="login-form__input"
                id="email"
                name="email"
                type="email"
                defaultValue={old_email}
                required
                autoFocus
                {...(constraints.email.pattern && { pattern: constraints.email.pattern })}
                {...(constraints.email.maxlength && { maxLength: constraints.email.maxlength })}
            />

            <label className="login-form__label" htmlFor="password">{t.password}</label>
            <input
                className="login-form__input"
                id="password"
                name="password"
                type="password"
                required
                {...(constraints.password.minlength && { minLength: constraints.password.minlength })}
                {...(constraints.password.maxlength && { maxLength: constraints.password.maxlength })}
            />

            <button className="login-form__button" type="submit">
                {t.submit}
            </button>

            <div className="login-form__links">
                <a href={forgot_password_url} className="login-form__link">{t_forgot_password}</a>
                <a href={register_url} className="login-form__link">{t_register}</a>
                <a href={home_url} className="login-form__link">{t_home}</a>
            </div>
        </form>
    );
}
