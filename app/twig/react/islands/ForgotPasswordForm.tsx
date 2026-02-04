interface InputConstraints {
    pattern?: string | null;
    maxlength?: number | null;
}

interface ForgotPasswordFormProps {
    action: string;
    errors?: string[];
    old_email?: string;
    t: {
        title: string;
        email: string;
        submit: string;
    };
    constraints: {
        email: InputConstraints;
    };
    login_url: string;
    t_back_to_login: string;
}

export default function ForgotPasswordForm({ action, errors = [], old_email = '', t, constraints, login_url, t_back_to_login }: ForgotPasswordFormProps) {
    return (
        <form method="post" action={action} className="login-form">
            <h1 className="login-form__title">{t.title}</h1>

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

            <button className="login-form__button" type="submit">
                {t.submit}
            </button>

            <div className="login-form__links">
                <a href={login_url} className="login-form__link">{t_back_to_login}</a>
            </div>
        </form>
    );
}
