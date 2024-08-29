// Import necessary modules from React and WordPress
import React from "react";
import { createRoot } from "react-dom/client";
import { useState, useEffect } from "@wordpress/element";
import { SelectControl, Notice, Button, TextControl, RangeControl } from "@wordpress/components";
const { __ } = wp.i18n;
import "./scss/style.scss";


// Define the main component for the Posts Maintenance App
const PostsMaintenanceApp = () => {
	// Declare a state variable 'message' with an initial empty string value
	const [selectedPostTypes, setSelectedPostTypes] = useState([]);
	const [notice, setNotice] = useState(null);
	const [postTypes, setPostTypes] = useState([]);
	const [selectedCategories, setSelectedCategories] = useState([]);
    const [ageThreshold, setAgeThreshold] = useState(365); // Default to 1 year
    const [engagementThreshold, setEngagementThreshold] = useState(10); // Default to 10
    const [categories, setCategories] = useState([]);


	useEffect(() => {
        // Get post types and categories data from localized script
        setPostTypes(LTG_Maintenance.postTypes);
        setCategories(LTG_Maintenance.categories);
    }, []);


	// Function to handle the scanning of posts
	const handleScanPosts = async () => {
		// Update the 'message' state to indicate scanning has started
		setNotice({ type: "success", message: "Scanning posts..." });

		try {
			const response = await fetch(LTG_Maintenance.restEndpoint, {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					"X-WP-Nonce": LTG_Maintenance.nonce,
				},
				body: JSON.stringify({
                    post_types: selectedPostTypes,
                    categories: selectedCategories,
                    age_threshold: ageThreshold,
                    engagement_threshold: engagementThreshold,
                }),
			});

			// Parse the JSON response
			const data = await response.json();

			// Set the notice state based on the API response
			if (response.ok) {
				setNotice({ type: "success", message: data.message });
			} else {
				setNotice({
					type: "error",
					message: data.message || "An error occurred.",
				});
			}
		} catch (error) {
			setNotice({
				type: "error",
				message: data.message || "An error occurred.",
			});
		}
	};

	// Return the JSX structure for rendering the component
	return (
		<div>
			<h1>{__("Posts Maintenance", "ltg-posts-maintenance")}</h1>

			<p>
                {__(
                    "Click the button below to scan posts based on the selected criteria.",
                    "ltg-posts-maintenance",
                )}
            </p>
            <SelectControl
                multiple
                label={__("Select Post Types", "ltg-posts-maintenance")}
                value={selectedPostTypes}
                options={postTypes}
                onChange={(newValues) => setSelectedPostTypes(newValues)}
            />

            <SelectControl
                multiple
                label={__("Select Categories", "ltg-posts-maintenance")}
                value={selectedCategories}
                options={categories}
                onChange={(newValues) => setSelectedCategories(newValues)}
            />

            <TextControl
                label={__("Age Threshold (days)", "ltg-posts-maintenance")}
                type="number"
                value={ageThreshold}
                onChange={(value) => setAgeThreshold(Number(value))}
                min="1"
            />

            <RangeControl
                label={__("Engagement Threshold", "ltg-posts-maintenance")}
                value={engagementThreshold}
                onChange={(value) => setEngagementThreshold(value)}
                min={0}
                max={100}
                step={1}
            />

			{notice && (
				<p>
					<Notice
						status={notice.type} // Set the type of notice (success/error)
						isDismissible={false}
					>
						{notice.message}
					</Notice>
				</p>
			)}

			<Button variant="primary" onClick={handleScanPosts}>
				{__("Scan Posts", "ltg-posts-maintenance")}
			</Button>
		</div>
	);
};

// Event listener for when the DOM content is fully loaded
document.addEventListener("DOMContentLoaded", () => {
	const el = document.getElementById("ltg-posts-maintenance-root"); // Get the target DOM element by ID

	if (el) {
        const root = createRoot(el); // Create a root
        root.render(<PostsMaintenanceApp />); // Render the component into the root
    }
});
