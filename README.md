## cohort-convert - a geneartion analysis tool.

Data analysis, converts time data (e.g. census data) into cohort data to analyze US generations. I developed the program to study the features of the Millennial generation as they became young adults in the 2000s.

Input data is user surveys taken over time, e.g. US Census data where the survey responder's age is known. The program takes the table (e.g. year = x axis, age of responder = y axis) and "rotates" the table 45 degrees, so that responses by cohorts to the same survey questions can be studied based on their advancing age. 

The goal of the project is twofold:

1. See which features of Millennials (and other generations) show a cohort effect, e.g. demographics.

2. Determine the degree to which the cohort "swimlanes" match generational models, e.g. the Strauss & Howe generations vs. the GenY model.

## Usage

The program operates with a front-end Web API.

1. An MS Excel file needs to be prepared (templates are present)
2. Template with data is uploaded via the web interface
3. The program outputs a new Excel file, with the survey data "rotated" 45 degrees to show how constant/variable cohort's responses to surveys is.
4. Resulting Excel file can be used to generate charts, graphics and other visualization output.

## Tech

The program was written in PHP, and avoids the use of a back-end database by reading and writing flatfile (Excel) files. It also leverages the huge set of array operations built into PHP.

