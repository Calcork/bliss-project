interface InputConstraints {
    minlength?: number | null;
    maxlength?: number | null;
}

interface ResetPasswordFormProps {
    action: string;
    token: string;
    errors?: string[];
    t: {
        title: string;
        password: string;
        submit: string;
    };
    constraints: {
        password: InputConstraints;
    };
    login_url: string;
    t_back_to_login: string;
}

export default function ResetPasswordForm({ action, token, errors = [], t, constraints, login_url, t_back_to_login }: ResetPasswordFormProps) {
    return (
        <form method="post" action={action} className="login-form">
            <h1 className="login-form__title">{t.title}</h1>

            <input type="hidden" name="token" value={token} />

            {errors.length > 0 && (
                <div className="login-form__errors">
                    {errors.map((error, i) => (
                        <p key={i}>{error}</p>
                    ))}
                </div>
            )}

            <label className="login-form__label" htmlFor="password">{t.password}</label>
            <input
                className="login-form__input"
                id="password"
                name="password"
                type="password"
                required
                autoFocus
                {...(constraints.password.minlength && { minLength: constraints.password.minlength })}
                {...(constraints.password.maxlength && { maxLength: constraints.password.maxlength })}
            />

            <button className="login-form__button" type="submit">
                {t.submit}
            </button>

            <div className="login-form__links">
                <a href={login_url} className="login-form__link">{t_back_to_login}</a>
            </div>
        </form>
    );
}
