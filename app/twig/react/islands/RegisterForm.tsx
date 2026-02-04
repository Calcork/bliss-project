import { useState, useEffect, useRef } from 'react';

interface InputConstraints {
    pattern?: string | null;
    minlength?: number | null;
    maxlength?: number | null;
}

interface RegisterFormProps {
    action: string;
    check_email_url: string;
    errors?: string[];
    old_name?: string;
    old_email?: string;
    t: {
        title: string;
        name: string;
        email: string;
        password: string;
        submit: string;
        login: string;
        email_available: string;
        email_taken: string;
        checking: string;
        home: string;
    };
    constraints: {
        name: InputConstraints;
        email: InputConstraints;
        password: InputConstraints;
    };
    login_url: string;
    home_url: string;
}

type EmailStatus = 'idle' | 'checking' | 'available' | 'taken';

export default function RegisterForm({
    action,
    check_email_url,
    errors = [],
    old_name = '',
    old_email = '',
    t,
    constraints,
    login_url,
    home_url,
}: RegisterFormProps) {
    const [email, setEmail] = useState(old_email);
    const [email_status, setEmailStatus] = useState<EmailStatus>('idle');
    const timer_ref = useRef<ReturnType<typeof setTimeout> | null>(null);
    const abort_ref = useRef<AbortController | null>(null);

    useEffect(() => {
        if (timer_ref.current) {
            clearTimeout(timer_ref.current);
        }

        if (abort_ref.current) {
            abort_ref.current.abort();
            abort_ref.current = null;
        }

        if (!email || !email.includes('@')) {
            setEmailStatus('idle');
            return;
        }

        setEmailStatus('checking');

        timer_ref.current = setTimeout(() => {
            const controller = new AbortController();
            abort_ref.current = controller;

            fetch(check_email_url + '?email=' + encodeURIComponent(email), {
                signal: controller.signal,
            })
                .then((res) => res.json())
                .then((data: { available: boolean }) => {
                    setEmailStatus(data.available ? 'available' : 'taken');
                })
                .catch((err) => {
                    if (err instanceof DOMException && err.name === 'AbortError') return;
                    setEmailStatus('idle');
                });
        }, 400);

        return () => {
            if (timer_ref.current) clearTimeout(timer_ref.current);
        };
    }, [email, check_email_url]);

    const status_text =
        email_status === 'checking'
            ? t.checking
            : email_status === 'available'
              ? t.email_available
              : email_status === 'taken'
                ? t.email_taken
                : null;

    const status_class =
        email_status === 'available'
            ? 'register-form__email-status--available'
            : email_status === 'taken'
              ? 'register-form__email-status--taken'
              : '';

    return (
        <form method="post" action={action} className="register-form">
            <h1 className="register-form__title">{t.title}</h1>

            {errors.length > 0 && (
                <div className="register-form__errors">
                    {errors.map((error, i) => (
                        <p key={i}>{error}</p>
                    ))}
                </div>
            )}

            <label className="register-form__label" htmlFor="name">
                {t.name}
            </label>
            <input
                className="register-form__input"
                id="name"
                name="name"
                type="text"
                defaultValue={old_name}
                required
                autoFocus
                {...(constraints.name.minlength && { minLength: constraints.name.minlength })}
                {...(constraints.name.maxlength && { maxLength: constraints.name.maxlength })}
            />

            <label className="register-form__label" htmlFor="email">
                {t.email}
            </label>
            <div className="register-form__email-wrap">
                <input
                    className="register-form__input"
                    id="email"
                    name="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    {...(constraints.email.pattern && { pattern: constraints.email.pattern })}
                    {...(constraints.email.maxlength && { maxLength: constraints.email.maxlength })}
                />
                {status_text && (
                    <span className={`register-form__email-status ${status_class}`}>
                        {status_text}
                    </span>
                )}
            </div>

            <label className="register-form__label" htmlFor="password">
                {t.password}
            </label>
            <input
                className="register-form__input"
                id="password"
                name="password"
                type="password"
                required
                {...(constraints.password.minlength && { minLength: constraints.password.minlength })}
                {...(constraints.password.maxlength && { maxLength: constraints.password.maxlength })}
            />

            <button className="register-form__button" type="submit">
                {t.submit}
            </button>

            <div className="register-form__links">
                <a href={login_url} className="register-form__link">{t.login}</a>
                <a href={home_url} className="register-form__link">{t.home}</a>
            </div>
        </form>
    );
}
