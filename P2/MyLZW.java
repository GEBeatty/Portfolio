/*************************************************************************
 *  Compilation:  javac LZW.java
 *  Execution:    java LZW - < input.txt   (compress)
 *  Execution:    java LZW + < input.txt   (expand)
 *  Dependencies: BinaryIn.java BinaryOut.java
 *
 *  Compress or expand binary input from standard input using LZW.
 *
 *  WARNING: STARTING WITH ORACLE JAVA 6, UPDATE 7 the SUBSTRING
 *  METHOD TAKES TIME AND SPACE LINEAR IN THE SIZE OF THE EXTRACTED
 *  SUBSTRING (INSTEAD OF CONSTANT SPACE AND TIME AS IN EARLIER
 *  IMPLEMENTATIONS).
 *
 *  See <a href = "http://java-performance.info/changes-to-string-java-1-7-0_06/">this article</a>
 *  for more details.
 *
 * 
 * This code is modified from the original LZW.java file and is now
 * MyLZW.java
 * 
 * Grace Beatty
 * 2/25/19
 * Project 2: LZW various compression techniques
 ************************************************************************/

public class MyLZW {
    private static final int R = 256;        // number of input chars
    private static int W = 9;         // codeword width - will be variable (9-16)
    private static int L = 512;       // number of codewords = 2^W (will be expanded upon)

    /*
     * Do-nothing compression algorithm
     * This algorithm runs the same way that LZW.java runs, with the 9-16 bit modification.
     */
    public static void compress() { 
        // System.err.println("\n   One moment please . . .\n");
        // Read in the file
        String input = BinaryStdIn.readString();
        // Create the TST to hold the prefix/codeword pairs
        TST<Integer> st = new TST<Integer>();
        // Place every alphabet character into the file (takes up first 256)
        for (int i = 0; i < R; i++)
            st.put("" + (char) i, i);
        int code = R+1;  // R is codeword for EOF (end of file)

        // Mark this file as Do-nothing encoding
        BinaryStdOut.write('n', 8);

        // Going through the file
        while (input.length() > 0) {
            String s = st.longestPrefixOf(input);  // Find max prefix match s.
            BinaryStdOut.write(st.get(s), W);      // Print s's encoding to file.
            int t = s.length();
            // If we aren't at the end of our prefix, and the code book isn't full
            if (t < input.length() && code < L)    // Add s to symbol table.
            {
                st.put(input.substring(0, t + 1), code++);
            }
            // Otherwise, we need to consider increasing our bit width and expanding our codebook
            else if (t < input.length() && code == L && W < 16)
            {
                // Increase our bit width and our codebook integer size
                W += 1; L = (int) Math.pow(2, W);
                // Now add s to the symbol table
                st.put(input.substring(0, t + 1), code++);
            }

            input = input.substring(t);            // Scan past s in input.
        }
        // Write out alphabet size and codeword width to end of file
        BinaryStdOut.write(R, W);
        BinaryStdOut.close();
        // System.err.println("\n   Finished!");
    }

    /*
     * Reset compression algorithm. This algorithm keeps track of the codebook, and when it becomes
     * totally full, and we've reached our 16-bit codeword limit, it resets the entire codebook and
     * sets the codeword size back to 9.
     */
    public static void resetCompress()
    {
        // System.err.println("\n   One moment please . . .\n");
        // Read in the file
        String input = BinaryStdIn.readString();
        // Create the TST to hold the prefix/codeword pairs
        TST<Integer> st = new TST<Integer>();
        // Place every alphabet character into the file (takes up first 256)
        for (int i = 0; i < R; i++)
            st.put("" + (char) i, i);
        int code = R+1;  // R is codeword for EOF (end of file)

        // Mark this file as Reset encoding
        BinaryStdOut.write('r', 8);

        // Going through the file
        while (input.length() > 0) {
            String s = st.longestPrefixOf(input);  // Find max prefix match s.
            BinaryStdOut.write(st.get(s), W);      // Print s's encoding to file.
            int t = s.length();
            // If we aren't at the end of our prefix, and the code book isn't full
            if (t < input.length() && code < L)    // Add s to symbol table.
            {
                st.put(input.substring(0, t + 1), code++);
            }
            // Otherwise, we need to consider increasing our bit width and expanding our codebook
            else if (t < input.length() && code == L && W < 16)
            {
                // Increase our bit width and our codebook integer size
                W += 1; L = (int) Math.pow(2, W);
                // Now add s to the symbol table
                st.put(input.substring(0, t + 1), code++);
            }
            // Once we've reached our code limit, we need to totally reset the codebook
            else if (t < input.length() && code == L && W == 16)
            {
                // Need to reset our W and L
                W = 9; L = 512;
                st = new TST<Integer>();
                // Place every alphabet character into the file (takes up first 256)
                for (int i = 0; i < R; i++)
                    st.put("" + (char) i, i);
                code = R+1;  // R is codeword for EOF (end of file)
                // Now add s to the new symbol table
                st.put(input.substring(0, t + 1), code++);
            }

            input = input.substring(t);            // Scan past s in input.
        }
        // Write out alphabet size and codeword width to end of file
        BinaryStdOut.write(R, W);
        BinaryStdOut.close();
        // System.err.println("\n   Finished!");
    }

    public static void monitorCompress()
    {
        // System.err.println("\n   One moment please . . .\n");
        // Read in the file
        String input = BinaryStdIn.readString();
        // Create the TST to hold the prefix/codeword pairs
        TST<Integer> st = new TST<Integer>();
        // Place every alphabet character into the file (takes up first 256)
        for (int i = 0; i < R; i++)
            st.put("" + (char) i, i);
        int code = R+1;  // R is codeword for EOF (end of file)
        // Two integers, to keep track of how many bits I have in each file
        double uncompressSum = 0;
        double compressSum = 0;
        double oldRatio = 0;
        double newRatio = 0;
        double totalRatio = 0;

        // Mark this file as Monitor encoding
        BinaryStdOut.write('m', 8);

        // Going through the file
        while (input.length() > 0) {
            String s = st.longestPrefixOf(input);  // Find max prefix match s.
            uncompressSum += (s.length() * 8);     // Increase the uncompressed sum by # of characters
            BinaryStdOut.write(st.get(s), W);      // Print s's encoding to file.
            compressSum += W;                      // Increase the compressed sum by the number of bits we're writing out
            int t = s.length();
            // If we aren't at the end of our prefix, and the code book isn't full
            if (t < input.length() && code < L)    // Add s to symbol table.
            {
                st.put(input.substring(0, t + 1), code++);
            }
            // Otherwise, we need to consider increasing our bit width and expanding our codebook
            else if (t < input.length() && code == L && W < 16)
            {
                // Increase our bit width and our codebook integer size
                W += 1; L = (int) Math.pow(2, W);
                // Now add s to the symbol table
                st.put(input.substring(0, t + 1), code++);
            }
            // Once our codebook reaches full, we need to start monitoring our compression ratio
            else if (t < input.length() && code == L && W == 16)
            {
                // The string S has how many characters are read in at each moment. Each character is 8 bits long
                // Our codeword we write out is exactly W bits long, and will vary as our compression continues.
                newRatio = uncompressSum / compressSum;
                if(oldRatio == 0)   // Should be when we first fill the codebook
                {
                    // Set oldRatio
                    oldRatio = newRatio;
                } else {    // We compare our ratios
                    totalRatio = oldRatio / newRatio;
                    // If we reach this threshold, we need to start over.
                    if(totalRatio > 1.1)
                    {
                        // Need to reset our W and L, and make a new trie
                        W = 9; L = 512;
                        st = new TST<Integer>();
                        // Place every alphabet character into the file (takes up first 256)
                        for (int i = 0; i < R; i++)
                            st.put("" + (char) i, i);
                        code = R+1;  // R is codeword for EOF (end of file)
                        // Now add s to the new symbol table
                        st.put(input.substring(0, t + 1), code++);
                        // Reset ratios
                        oldRatio = 0; newRatio = 0;
                    }
                }
            }
            input = input.substring(t);            // Scan past s in input.
        }
        // Write out alphabet size and codeword width to end of file
        BinaryStdOut.write(R, W);
        BinaryStdOut.close();
        // System.err.println("\n   Finished!");
    }

    /*
     * Expansion algorithm
     * Should be able to handle expansion from all 3 formats (n, r, m)
     */
    public static void expand() {
        // System.err.println("\n   One moment please . . .\n");
        String[] st = new String[L];    // Codeword array
        int i; // next available codeword value
        double uncompressed = 0; // The uncompressed information
        double compressed = 0;   // The compressed information
        double oldRatio = 0;   // The two ratios
        double newRatio = 0;
        double totalRatio = 0;

        // initialize symbol table with all 1-character strings
        for (i = 0; i < R; i++)  // 'i' is our equivalent 'code' variable
            st[i] = "" + (char) i;
        st[i++] = "";              // (unused) lookahead for EOF

        // Read and see what type of encoding it is (n, r, or m)
        char encoding = BinaryStdIn.readChar();

        if(encoding == 'n')  // Do nothing
        {
            int codeword = BinaryStdIn.readInt(W); // Read in the next codeword of W bits
            if (codeword == R) return;             // Expanded message is empty string
            String val = st[codeword];             // Grab the first value using the codeword

            // Begin the loop to read the expand the rest of the file.
            while (true) {
                BinaryStdOut.write(val);              // Write out the value to file
                codeword = BinaryStdIn.readInt(W);    // Read in the next word
                if (codeword == R) break;             // EOF break
                String s = st[codeword];              // Grab the next codeword
                if (i == codeword) s = val + val.charAt(0);   // special case hack
                // If no expansion, it's ok
                if (i < L) st[i++] = val + s.charAt(0);       // Add the next codeword to the table
                // We need to consider our expansion
                if (i == L && W < 16)
                {
                    W +=1; L = (int) Math.pow(2, W);
                    String[] temp = new String[L];
                    // Fill the temp array
                    for(int k = 0; k < st.length; k++)
                    {
                        temp[k] = st[k];
                    }
                    // Change the st reference
                    st = temp;
                }
                val = s;
            }
        }
        else if(encoding == 'r')  // Reset
        {
            int codeword = BinaryStdIn.readInt(W); // Read in the next codeword of W bits
            if (codeword == R) return;             // Expanded message is empty string
            String val = st[codeword];             // Grab the first value using the codeword

            // Begin the loop to read the expand the rest of the file.
            while (true) {
                BinaryStdOut.write(val);              // Write out the value to file
                codeword = BinaryStdIn.readInt(W);    // Read in the next word
                if (codeword == R) break;             // EOF break
                String s = st[codeword];              // Grab the next codeword
                if (i == codeword) s = val + val.charAt(0);   // special case hack
                // If no expansion, it's ok
                if (i < L) st[i++] = val + s.charAt(0);       // Add the next codeword to the table
                // We need to consider our expansion
                if (i == L && W < 16)
                {
                    W +=1; L = (int) Math.pow(2, W);
                    String[] temp = new String[L];
                    // Fill the temp array
                    for(int k = 0; k < st.length; k++)
                    {
                        //System.err.println("k is " + k + ". temp size is " + temp.length + " and st size is " + st.length);
                        temp[k] = st[k];
                    }
                    // Change the st reference
                    st = temp;
                }
                if (i == L && W == 16) // We've hit our code limit
                {
                    // Need to reset our L and W
                    W = 9; L = 512;
                    String[] temp = new String[L];    // new codeword array
                    for (i = 0; i < R; i++)           // 'i' is reset here
                        temp[i] = "" + (char) i;      // Fill new array with alphabet
                    temp[i++] = "";  // (unused) EOF
                    // Change the st reference
                    st = temp;
                }
                val = s;
            }
        }
        else if (encoding == 'm')  // Monitor
        {
            int codeword = BinaryStdIn.readInt(W); // Read in the next codeword of W bits
            if (codeword == R) return;             // Expanded message is empty string
            String val = st[codeword];             // Grab the first value using the codeword
            compressed += W; uncompressed += (val.length() * 8);

            // Begin the loop to read the expand the rest of the file.
            while (true) {
                BinaryStdOut.write(val);              // Write out the value to file
                codeword = BinaryStdIn.readInt(W);    // Read in the next word
                if (codeword == R) break;             // EOF break
                String s = st[codeword];              // Grab the next codeword
                if (i == codeword) s = val + val.charAt(0);   // special case hack
                compressed += W; uncompressed += (s.length() * 8);  // Considered after special case
                // If no expansion, it's ok
                if (i < L) st[i++] = val + s.charAt(0);       // Add the next codeword to the table
                // We need to consider our expansion
                if (i == L && W < 16)
                {
                    W +=1; L = (int) Math.pow(2, W);
                    String[] temp = new String[L];
                    // Fill the temp array
                    for(int k = 0; k < st.length; k++)
                    {
                        //System.err.println("k is " + k + ". temp size is " + temp.length + " and st size is " + st.length);
                        temp[k] = st[k];
                    }
                    // Change the st reference
                    st = temp;
                }
                if (i == L && W == 16) // We've hit limit, time to monitor
                {
                    newRatio = uncompressed / compressed;
                    if(oldRatio == 0){
                        // Set oldRatio
                        oldRatio = newRatio;
                    } else {
                        totalRatio = oldRatio / newRatio;
                        if(totalRatio > 1.1)    // Our ratio is too high: reset!
                        {
                            // Need to reset our L and W
                            W = 9; L = 512;
                            String[] temp = new String[L];    // new codeword array
                            for (i = 0; i < R; i++)           // 'i' is reset here
                                temp[i] = "" + (char) i;      // Fill new array with alphabet
                            temp[i++] = "";  // (unused) EOF
                            // Change the st reference
                            st = temp;
                            oldRatio = 0; newRatio = 0;
                        }
                    }
                }
                val = s;
            }
        }
        
        BinaryStdOut.close();
        // System.err.println("\n   Finished!");
    }

    /*
    *   The main method for this class. Takes in two arguments from the command
    *   line, (n, r, m) for mode and (-, +) for either compression or expansion.
    */
    public static void main(String[] args) {
        if      (args[0].equals("-") && args[1].equals("n")) compress();  // Do-nothing
        else if (args[0].equals("-") && args[1].equals("r")) resetCompress(); // Reset
        else if (args[0].equals("-") && args[1].equals("m")) monitorCompress(); // Monitor
        else if (args[0].equals("+")) expand();  // Expand
        else throw new IllegalArgumentException("Illegal command line argument");
    }

}
