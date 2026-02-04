import { createRoot } from 'react-dom/client';
import islands from './islands';

document.querySelectorAll<HTMLElement>('[data-react]').forEach((el) => {
    const name = el.dataset.react;
    if (!name || !islands[name]) {
        console.warn(`React island "${name}" not found`);
        return;
    }

    const Component = islands[name];
    const props = el.dataset.props ? JSON.parse(el.dataset.props) : {};

    createRoot(el).render(<Component {...props} />);
});
