export function createModuleSelectionState() {
    let selectedModule = null;
    const listeners = new Set();

    const notify = () => listeners.forEach((listener) => listener(selectedModule));

    return {
        select(module) {
            selectedModule = module;
            notify();
        },
        clear() {
            selectedModule = null;
            notify();
        },
        subscribe(listener) {
            listeners.add(listener);
            listener(selectedModule);

            return () => listeners.delete(listener);
        },
    };
}

export function createTopicSelectionState() {
    let selectedTopic = null;
    const listeners = new Set();

    const notify = () => listeners.forEach((listener) => listener(selectedTopic));

    return {
        select(topic) {
            selectedTopic = topic;
            notify();
        },
        clear() {
            selectedTopic = null;
            notify();
        },
        subscribe(listener) {
            listeners.add(listener);
            listener(selectedTopic);

            return () => listeners.delete(listener);
        },
    };
}
