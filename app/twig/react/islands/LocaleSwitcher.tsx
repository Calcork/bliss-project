import { useState, useEffect, useRef } from 'react';

interface LocaleLink {
    locale: string;
    name: string;
    url: string;
    active: boolean;
}

interface LocaleSwitcherProps {
    links: LocaleLink[];
}

export default function LocaleSwitcher({ links }: LocaleSwitcherProps) {
    const [open, setOpen] = useState(false);
    const ref = useRef<HTMLDivElement>(null);

    const active = links.find((l) => l.active);
    const others = links.filter((l) => !l.active);

    useEffect(() => {
        function handleClickOutside(e: MouseEvent) {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }

        if (open) {
            document.addEventListener('click', handleClickOutside);
            return () => document.removeEventListener('click', handleClickOutside);
        }
    }, [open]);

    return (
        <div className={`locale-switcher${open ? ' locale-switcher--open' : ''}`} ref={ref}>
            <button
                type="button"
                className="locale-switcher__toggle"
                onClick={() => setOpen(!open)}
            >
                {active?.name}
                <span className="locale-switcher__arrow">&#9662;</span>
            </button>
            <div className="locale-switcher__dropdown">
                {others.map((link) => (
                    <a key={link.locale} href={link.url} className="locale-switcher__item">
                        {link.name}
                    </a>
                ))}
            </div>
        </div>
    );
}
