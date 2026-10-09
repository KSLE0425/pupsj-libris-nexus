<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\CollectionType;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $filipiniana = CollectionType::where('name', 'Filipiniana')->first()?->id ?? 1;
        $locCollection = CollectionType::where('name', 'Library of Congress')->first()?->id ?? 2;
        $thesis = CollectionType::where('name', 'Thesis Collection')->first()?->id ?? 3;
        $fictions = CollectionType::where('name', 'Fictions')->first()?->id ?? 4;
        $special = CollectionType::where('name', 'Special Collections')->first()?->id ?? 5;
        $general = CollectionType::where('name', 'General')->first()?->id ?? 6;

        $books = [
            // 1. Filipiniana Books
            [
                'title'               => 'Noli Me Tangere',
                'author'              => 'Jose Rizal',
                'isbn'                => '978-971-08-4355-8',
                'accession_number'    => 'ACC-FIL-001',
                'barcode'             => 'PUP-FIL-001',
                'subject'             => 'Philippine Literature, History, Political Fiction',
                'keywords'            => 'Rizal, Noli, Philippines, Classic, Novel',
                'publisher'           => 'Instituto Nacional de Historia',
                'publication_year'    => 1995,
                'edition'             => 'Centennial Edition',
                'collection'          => 'Filipiniana',
                'loc_number'          => 'P',
                'status'              => 'available',
                'copies'              => 5,
                'category'            => 'Filipiniana Literature',
                'is_new_acquisition'  => false,
                'shelf_location'      => 'Shelf FIL-A1',
                'collection_type_id'  => $filipiniana,
                'is_donation'         => false,
                'is_filipino_author'  => true,
                'is_ph_published'     => true,
                'is_ph_subject'       => true,
            ],
            [
                'title'               => 'El Filibusterismo',
                'author'              => 'Jose Rizal',
                'isbn'                => '978-971-08-4356-5',
                'accession_number'    => 'ACC-FIL-002',
                'barcode'             => 'PUP-FIL-002',
                'subject'             => 'Philippine Literature, History, Revolution',
                'keywords'            => 'Rizal, Fili, Simoun, Revolution, Novel',
                'publisher'           => 'Guerrero Publishing',
                'publication_year'    => 1998,
                'edition'             => 'Revised Edition',
                'collection'          => 'Filipiniana',
                'loc_number'          => 'P',
                'status'              => 'available',
                'copies'              => 4,
                'category'            => 'Filipiniana Literature',
                'is_new_acquisition'  => false,
                'shelf_location'      => 'Shelf FIL-A1',
                'collection_type_id'  => $filipiniana,
                'is_donation'         => false,
                'is_filipino_author'  => true,
                'is_ph_published'     => true,
                'is_ph_subject'       => true,
            ],

            // 2. Science & Tech (LOC Q / T)
            [
                'title'               => 'Introduction to Algorithms (4th Edition)',
                'author'              => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                'isbn'                => '978-0262046305',
                'accession_number'    => 'ACC-CS-001',
                'barcode'             => 'PUP-CS-001',
                'subject'             => 'Computer Science, Algorithms, Data Structures',
                'keywords'            => 'Algorithms, CLRS, Programming, Sorting, Graph Algorithms',
                'publisher'           => 'MIT Press',
                'publication_year'    => 2022,
                'edition'             => '4th Edition',
                'collection'          => 'Library of Congress',
                'loc_number'          => 'QA76.6',
                'status'              => 'available',
                'copies'              => 3,
                'category'            => 'Computer Science',
                'is_new_acquisition'  => true,
                'shelf_location'      => 'Shelf CS-B2',
                'collection_type_id'  => $locCollection,
                'is_donation'         => false,
                'is_filipino_author'  => false,
                'is_ph_published'     => false,
                'is_ph_subject'       => false,
            ],
            [
                'title'               => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author'              => 'Robert C. Martin',
                'isbn'                => '978-0132350884',
                'accession_number'    => 'ACC-CS-002',
                'barcode'             => 'PUP-CS-002',
                'subject'             => 'Software Engineering, Clean Code, Agile Development',
                'keywords'            => 'Uncle Bob, Refactoring, Design Patterns, Code Quality',
                'publisher'           => 'Prentice Hall',
                'publication_year'    => 2008,
                'edition'             => '1st Edition',
                'collection'          => 'Library of Congress',
                'loc_number'          => 'QA76.76.D47',
                'status'              => 'available',
                'copies'              => 2,
                'category'            => 'Computer Science',
                'is_new_acquisition'  => false,
                'shelf_location'      => 'Shelf CS-B2',
                'collection_type_id'  => $locCollection,
                'is_donation'         => true,
                'is_filipino_author'  => false,
                'is_ph_published'     => false,
                'is_ph_subject'       => false,
            ],

            // 3. Business & Management (LOC H)
            [
                'title'               => 'Principles of Marketing (18th Edition)',
                'author'              => 'Philip Kotler, Gary Armstrong',
                'isbn'                => '978-0135766606',
                'accession_number'    => 'ACC-BUS-001',
                'barcode'             => 'PUP-BUS-001',
                'subject'             => 'Business Administration, Marketing Strategy, Consumer Behavior',
                'keywords'            => 'Kotler, Marketing, Branding, Advertising, Business',
                'publisher'           => 'Pearson',
                'publication_year'    => 2020,
                'edition'             => '18th Global Edition',
                'collection'          => 'Library of Congress',
                'loc_number'          => 'HF5415',
                'status'              => 'available',
                'copies'              => 4,
                'category'            => 'Business & Marketing',
                'is_new_acquisition'  => true,
                'shelf_location'      => 'Shelf BUS-C3',
                'collection_type_id'  => $locCollection,
                'is_donation'         => false,
                'is_filipino_author'  => false,
                'is_ph_published'     => false,
                'is_ph_subject'       => false,
            ],

            // 4. Thesis & Capstone Collection
            [
                'title'               => 'PUP SJ LibrisNexus: AI-Powered Library Management and Book Discovery System',
                'author'              => 'BSIT Batch 2024 Research Group 1',
                'isbn'                => null,
                'accession_number'    => 'ACC-THESIS-2024-01',
                'barcode'             => 'PUP-THESIS-2024-01',
                'subject'             => 'Library Automation, Artificial Intelligence, Web Application',
                'keywords'            => 'Capstone, PUP SJ, Library System, Laravel, Vue, AI',
                'publisher'           => 'Polytechnic University of the Philippines San Juan Campus',
                'publication_year'    => 2024,
                'edition'             => 'Final Hardbound',
                'collection'          => 'Thesis Collection',
                'loc_number'          => 'Z678',
                'status'              => 'available',
                'copies'              => 2,
                'category'            => 'Information Technology Thesis',
                'is_new_acquisition'  => true,
                'shelf_location'      => 'Shelf THESIS-2024',
                'collection_type_id'  => $thesis,
                'research_type'       => 'capstone',
                'is_donation'         => false,
                'is_filipino_author'  => true,
                'is_ph_published'     => true,
                'is_ph_subject'       => true,
            ],

            // 5. Fiction / Novel
            [
                'title'               => 'To Kill a Mockingbird',
                'author'              => 'Harper Lee',
                'isbn'                => '978-0061120084',
                'accession_number'    => 'ACC-FIC-001',
                'barcode'             => 'PUP-FIC-001',
                'subject'             => 'Classic Literature, Fiction, Legal Drama',
                'keywords'            => 'Atticus Finch, Scout, Racism, Justice, Novel',
                'publisher'           => 'Harper Perennial Modern Classics',
                'publication_year'    => 2006,
                'edition'             => '50th Anniversary Edition',
                'collection'          => 'Fictions',
                'loc_number'          => 'PS3562.E353',
                'status'              => 'available',
                'copies'              => 3,
                'category'            => 'General Fiction',
                'is_new_acquisition'  => false,
                'shelf_location'      => 'Shelf FIC-D1',
                'collection_type_id'  => $fictions,
                'is_donation'         => true,
                'is_filipino_author'  => false,
                'is_ph_published'     => false,
                'is_ph_subject'       => false,
            ],
        ];

        foreach ($books as $index => $b) {
            if (empty($b['accession_number'])) {
                $b['accession_number'] = 'ACC-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT);
            }
            Book::updateOrCreate(
                ['barcode' => $b['barcode']],
                $b
            );
        }
    }
}
