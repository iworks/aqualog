# iWorks Aquarium Tracker: Water Parameters, Reminders & Notes

Effortless plugin for aquarium pros and hobbyists. Track multiple tanks with pH, nitrates, temp, and custom parameters. Tag maintenance like water changes, set reminders, and add notes with photos. Multi-tank dashboards, trend charts, and email alerts keep everything on point. Log smarter – watch your tanks flourish!

## Gutenberg Blocks

This plugin includes custom Gutenberg blocks to enhance your WordPress editor with aquarium-specific functionality.

### Available Blocks

#### Aquarium Block
- **Block Name:** `aqualog/aqualog-aquarium`
- **Description:** Display a selected aquarium with its title and details
- **Features:**
  - Select from your registered aquariums
  - Displays aquarium title in the editor and on the frontend
  - Supports dynamic content loading
  - Integrates with the Aqualog post type

### Block Development

Blocks are located in the `assets/blocks/` directory. Each block follows the standard WordPress block structure:

```
assets/blocks/
└── aqualog-aquarium/
    ├── src/
    │   └── aqualog-aquarium/
    │       ├── block.json    # Block configuration
    │       ├── edit.js        # Editor interface
    │       ├── save.js        # Frontend rendering
    │       └── index.js       # Block registration
    ├── build/                 # Compiled assets
    └── aqualog-aquarium.php   # Server-side registration
```

### Building Blocks

To build blocks for development:

```bash
npm install
npm run build
```

For development with hot reloading:

```bash
npm run start
```

### Block Templates

Custom block templates are stored in `assets/templates/blocks/` and can be loaded dynamically by the plugin for server-side rendering.

