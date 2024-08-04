
# Swell Scales WordPress Plugin
Welcome to the Swell Scales WordPress plugin! This plugin is inspired by the application of concepts underlying musical scales to web typography. It provides advanced typographical and color management features, including responsive typographic scales, dynamic color classes, and a wide selection of Google Fonts couples. Additionally, it includes automatic SCSS compilation and the ability to add custom SCSS.

## Features and usage

### 1. Responsive Typographic Scales
Swell Scales helps you create responsive typographic scales effortlessly. By configuring a few parameters at **/scss/typo-scales/_typo-scale-map.scss**, you can generate consistent and scalable typographic scales across different screen sizes.

**Generated pettern class:**
```scss
.text-{screen-size}-{typographic-interval}
```

**Key Parameters:**
- **Base Size**: Defines the root font size in px for your typography.
- **Increment Factor**: Controls the growth rate of font sizes at different breakpoints.
- **Ratio**: Determines the scale ratio for font sizes.

**Example Configuration:**
```scss
$responsiveBaseFontSize: (
  baseSize: 17,
  incrementFactor: 1.05
);

$customFontSizeScale: (
  r: 1.25,
);
```

With these settings, Swell Scales generates a typographic scale based on the specified base size, increment factor, and ratio. The plugin also supports responsive font sizes, ensuring your typography adapts smoothly to different screen sizes.

### 2. Dynamic Color Classes
Create and manage a wide range of color classes easily with Swell Scales. The plugin supports solid colors and gradients, generating corresponding utility classes for background colors, text colors, borders, and fill.

**Color Management:**
Define color variables at **/scss/colors/_color-map.scss**
Generate CSS classes for background, text, border, and fill colors.

**Example Configuration:**
```scss
$colors: (
  "yellow": #fbb03b,
  "red": #EC4040,
  "green": #56ca71,
  // Add more colors...
);
```

This setup generates classes like .bg-yellow, .text-red, and .border-green, ecc which you can use throughout your WordPress site for consistent styling.

In addition to the generated utility classes, you can access color values directly in your SCSS code using the get-color function. This function retrieves color values from the $colors map, allowing for dynamic and reusable color management.

**Function Definition:**
```scss
@function color($name) {
  @return map-get($colors, $name);
}
```

**Example Usage:**
```scss
body {
  background-color: color('light-blue');
  color: color('black');
}
```

### 3. Google Fonts Integration
Swell Scales provides access to **50 pairs of Google Fonts.** Easily integrate popular Google Fonts into your WordPress site and apply them to your typographic styles.

Import the selected font pair in  **scss/font-pairing/_font-pairing-import.scss**
The font pair partials are located in **scss/font-pairing/_font-pairing-list/** directory.

### 4. Automatic SCSS Compilation

Swell Scales automatically compiles SCSS files using **scssphp library** and enqueues the resulting style.min.css in the output directoty. This ensures that your styles are up-to-date without requiring manual compilation, just click on the button **Compile SCSS** in the WP admin bar.

### 5. Custom SCSS
You can add your own custom SCSS code by placing it in the **custom.scss** file within the plugin directory. This allows you to extend or override the default styles provided by Swell Scales.

## Installation

Upload the swell-scales plugin folder to the /wp-content/plugins/ directory.
Activate the plugin through the 'Plugins' menu in WordPress.

## Customization

For advanced customization, you can modify the plugin's SCSS files directly:

- **_typo-scale-logic.scss:** Adjust the logic for generating responsive typographic scales.
- **_color-logic.scss:** Manage global color variables and generate color utility classes.

## Support

For any issues or support requests, please contact me at **mic.paolino@gmail.com.**

Thank you for using Swell Scales! We hope this plugin helps you achieve beautiful and responsive typography and color management for your WordPress site.

### Inspiration for this plugin

https://spencermortensen.com/articles/typographic-scale/<br>
https://heyreliable.com/ultimate-google-font-pairings