import java.io.*;
import java.util.ArrayList;
import java.util.LinkedList;
import java.util.Queue;
import java.util.Scanner;
import java.util.concurrent.LinkedBlockingDeque;

/*
    Grace Beatty
    CS 1501 - Project 1 - Autocorrect program
    Last updated: 2/5/2019
*/

public class ac_test
{
    public static void main(String args[])
    {
        // Read in each part of the dictionary file to the trie
        File dict_file = new File("dictionary.txt");
        // Create the dictionary trie
        DLB_Trie dictionary_trie = new DLB_Trie();
        // The file is open, so we read in each line to the DLB trie one by one
        try
        {
            Scanner dict_read = new Scanner(dict_file); 
            String nextWord = "";
            while(dict_read.hasNextLine())
            {
                nextWord = dict_read.nextLine();
                nextWord = nextWord.trim();     // Just in case there's any white space
                dictionary_trie.put(nextWord);  // Put the next word into the trie
            }
            // finished reading in dictionary.txt
            dict_read.close();
        } catch (IOException e) {
            System.out.println("Ran into exception:\n" + e);
        }

        // Determine existance of a history file
        File hist_file = new File("history.txt");
        // Create the history trie
        DLB_Trie history_trie = new DLB_Trie();
        // If the file exists, load it into a trie
        if(hist_file.exists())
        {
            try
            {
                Scanner hist_read = new Scanner(hist_file);
                String histWord = "";
                String histNum = "";
                int histFreq = 0;
                while(hist_read.hasNextLine())
                {
                    histWord = hist_read.nextLine();    // Gets the word from the file
                    histWord = histWord.trim();
                    histNum = hist_read.nextLine();     // Gets the frequency number from the file before parsing
                    histFreq = Integer.parseInt(histNum);
                    history_trie.put(histWord, histFreq);   // Adds the word and frequency to the trie
                }
                // Finished reading in history.txt
                hist_read.close();
            } catch (IOException e) {
                System.out.println("Ran into exception:\n" + e);
            }
        }


        /*
            The main loop: autocorrect program
        */
        String userLetter = "0";        // To hold the user's entered letter
        Scanner input = new Scanner(System.in); // To accept user input
        boolean newWord = true;         // To hold the value for the loop of inputting a new word
        LinkedList<Node> suggest = new LinkedList<>();             // To hold suggestions for words
        Guesser theGuesser = new Guesser();     // The Guesser we'll use to find suggested words
        ArrayList<Double> times = new ArrayList<>();      // To hold all the nanotimes so we can average them out
        /*
            Now that the dictionary has been read in, we begin the program loop
            If the user enters in '!', then we need to end the loop and thereby end the progarm
        */
        while(userLetter.charAt(0) != '!')
        {
            //Begin by prompting the user for their next letter
            System.out.print("\nPlease enter your first letter: ");
            userLetter = input.nextLine();
            userLetter = userLetter.trim();     // Just in case there was any whitespace on there
            newWord = true;     // Set to begin a new word
            StringBuilder stb = new StringBuilder(userLetter);    // To build upon the user's word

            while(userLetter.charAt(0) != '!' && newWord == true)  // Using a second loop for sentence consistency's sake
            {
                // If the user selects a number, display the selection
                if(userLetter.charAt(0) == '1' || userLetter.charAt(0) == '2' || userLetter.charAt(0) == '3'
                    || userLetter.charAt(0) == '4' || userLetter.charAt(0) == '5')
                {
                    int userChoice = Integer.parseInt(userLetter);
                    userChoice -= 1;
                    System.out.println("\nYou chose " + suggest.get(userChoice).value + "\n");
                    // If the history trie doesn't contain the user's choice yet, put it in there
                    if(!history_trie.contains(suggest.get(userChoice)))
                    {
                        history_trie.put(suggest.get(userChoice).value);
                    }
                    else    // Increment the existing node's frequency
                    {
                        suggest.get(userChoice).freq += 1;
                    }
                    newWord = false;    // Start over
                }
                else if (userLetter.charAt(0) == '$') // User has signified they are finished typing
                {
                    // Print out the results
                    System.out.println("\nYour full word is " + stb.toString());
                    boolean inDict = dictionary_trie.contains(stb.toString());  // Does our dictionary contain that word?
                    if(!inDict)
                    {
                        System.out.println("Sorry, that word isn't in our dictionary.\n");
                    }
                    // If the word isn't in the history trie, put it there
                    if(!history_trie.contains(stb.toString()))
                    {
                        history_trie.put(stb.toString(), 0);
                    }
                    // But if it is in there, increase its frequency
                    else
                    {
                        Node increaseNode = history_trie.get(stb.toString(), true);
                        increaseNode.freq += 1;
                    } 
                    
                    newWord = false;    // Start over
                }
                else    // Continue asking for letters/symbols
                {
                    System.out.println("\nYour word so far: " + stb.toString() + "\n");
                    // Find 5 suggestions for the user and display them on screen
                    double firstTime = System.nanoTime();
                    // Pull from the history trie
                    suggest = theGuesser.guess(history_trie, stb.toString());


                    // The radix sorting for our history frequency will go here somewhere, connecting to a method


                    // Pull from the dictionary trie
                    suggest = theGuesser.guess(dictionary_trie, stb.toString(), suggest);
                    double secondTime = System.nanoTime();
                    double wholeTime = secondTime-firstTime;
                    wholeTime /= 1000000000;
                    times.add(wholeTime);
                    System.out.printf("(%f.4 s)%n", wholeTime);

                    // Display each suggestion
                    System.out.println("Available suggestions (Select by Number):");
                    int k = 0;
                    if(suggest != null)  // Need to make sure that the list isn't empty
                    {
                        while(k < suggest.size())
                        {
                            System.out.print((k+1) + ") " + suggest.get(k).value + "    ");
                            k++;
                        }
                    }
                    System.out.println();
                    
                    // Getting the next user input
                    System.out.print("\nPlease enter your next letter, or your selection: ");
                    userLetter = input.nextLine();
                    userLetter = userLetter.trim();     // Just in case there was any whitespace on there
                    if(userLetter.charAt(0) != '$')     // Don't append a '$'
                    {
                        stb.append(userLetter);   // Appending the new letter to the word
                    }
                    
                }
            }
        }
        // Since the program is finished, gather all of the words in the history trie and write them to history.txt
        LinkedList<Node> userHistory = new LinkedList<>();
        history_trie.getAllWords(userHistory);
        try
        {   // A try-catch for PrintWriter
            PrintWriter pw = new PrintWriter("history.txt");
            if(!userHistory.isEmpty())  // Can't do it if there's nothing in userHistory
            {
                for(int k = 0; k < userHistory.size(); k++) // Print each word into the file line by line
                {
                    pw.println(userHistory.get(k).value);   // Print word
                    pw.println(userHistory.get(k).freq);    // Print corresponding frequency
                }
            }
            // Finished, so close PrintWriter
            pw.close();
        } catch (IOException e) {
            System.out.println("Ran into exception: " + e);
        }
        
        // Close out the user input Scanner
        input.close();
        // Average out the times
        double average = 0;
        for(int k = 0; k < times.size(); k++)
        {
            average += times.get(k);
        }
        average /= times.size();    // Get average seconds 
        System.out.printf("Average time: %f.4 s%n", average);
        // Say goodbye
        System.out.println("\nGoodbye!");
    }
}

/*
    Guesser class: used to find words within a trie and return some guesses
*/
class Guesser
{
    LinkedList<Node> guessQueue;   // Going to store my guessing words here

    public Guesser()
    {
        guessQueue = new LinkedList<Node>();
    }

    /*
        Method guess: used to find some words from the given trie and give back guesses based on a prefix.
        It clears out the previous used list and creates a brand new one.
            DLB_Trie theTrie: the trie whose words we are searching
            String prefix: the prefix we're using to find the words
    */
    public LinkedList<Node> guess(DLB_Trie theTrie, String prefix)
    {
        guessQueue.clear(); // Make sure to clear out our previous list
        // We can't have empty or null prefixes
        if(prefix.equals("") || prefix.equals(null))
        {
            return guessQueue;
        }
        // We can do the following only if the trie isn't empty. Otherwise, we return the empty list
        if(!theTrie.isEmpty())
        {
            // Otherwise, first we need to find our place within the DLB
            Node ourNode = theTrie.get(prefix);
            if(ourNode == null) return guessQueue;  // Means that we didn't find anything
            // Now that we've found our starting point, we need to find all similar words
            theTrie.getRadixWords(guessQueue, ourNode, 0);
            // And then sort them by frequency of usage
            radixGuess();
        }
        return guessQueue;
    }

    public void radixGuess()
    {
        // We need an array to hold all of our frequencies, and another to hold our nodes
        int[] frequencies = new int[guessQueue.size()];
        Node[] nodes = new Node[guessQueue.size()];
        // Now we need to load those two arrays up with our values, so they line up.
        for(int k = 0; k < guessQueue.size(); k++)
        {
            frequencies[k] = guessQueue.get(k).freq;
            nodes[k] = guessQueue.get(k);
        }
        // Now we need to get our two arrays radix-sorted
        // First, we need to find the largest value in the list to make sure we don't go overboard
        int max = 0;
        for(int j = 0; j < guessQueue.size(); j++)
        {
            if(guessQueue.get(j).freq > max)    // Test each value at a time
            {
                max = guessQueue.get(j).freq;   // Switch out as necessary
            }
        }
        // Now that we have our value, we can sort.
        for(int m = 1; max/m > 0; m *= 10)
        {
            radixSort(frequencies, nodes, m);
        }
        // Our list is sorted from smallest to largest, so we need to add our nodes on backwards.
        int nodeAdd = guessQueue.size()-1;
        // Clear the guessQueue so we can add on our new nodes
        guessQueue.clear();
        // While we have more nodes to add, or until we get to 5 nodes added, we continue to add
        while(nodeAdd >= 0 && guessQueue.size() < 5)
        {
            guessQueue.add(nodes[nodeAdd]);
            nodeAdd--;
        }
        // And we're done!
    }

    public void radixSort(int[] fq, Node[] nd, int modSize)
    {
        // We need to make a count array for our counting
        int[] counting = new int[10];
        // And other for our sorted items
        int[] sortedf = new int[fq.length]; 
        Node[] sortedn = new Node[nd.length];
        // Count how many of each number is in the array
        for(int d = 0; d < fq.length; d++)
        {
            counting[(fq[d]/modSize)%10]++; // Take each number, determine its modulus by 10 for that position, and fix accordingly
        }
        // Change the counting array so that the positions are held, not just the counts
        for(int d = 1; d < 10; d++)
        {
            counting[d] += counting[d-1];
        }
        // Sort our input arrays based on the data within the counting arrays.
        for(int d = (fq.length-1); d >= 0; d--)
        {
            sortedf[(counting[fq[d]/modSize%10]-1)] = fq[d];
            sortedn[(counting[fq[d]/modSize%10]-1)] = nd[d];
            counting[fq[d]/modSize%10]--;
        }
        // Finally, return the sorted arrays to the original arrays to finish the sort
        for(int d = 0; d < fq.length; d++)
        {
            fq[d] = sortedf[d];
            nd[d] = sortedn[d];
        }
    }

    /*
        Method guess: used to find some words from the given trie and give back guesses based on a prefix.
        It does not clear out the previously used list, and instead adds to an already-existing one.
            DBL_Trie theTrie: the trie whose words we are searching
            String prefix: the prefix we're using to find the words
            LinkedList<Node> theList: The linked list that we're using to store the words
    */
    public LinkedList<Node> guess(DLB_Trie theTrie, String prefix, LinkedList<Node> theList)
    {
        // We can't have empty or null prefixes
        if(prefix.equals("") || prefix.equals(null))
        {
            return theList = null;
        }
        // Otherwise, first we need to find our place within the DLB
        Node ourNode = theTrie.get(prefix);
        if(ourNode == null) return null;  // Means that we didn't find anything
        // Now that we've found our starting point, we need to find similar words
        theTrie.getWords(theList, ourNode, 0);

        return theList;
    }
}

/*
    DLB_Trie class: used to store information in a dlb trie fashion
*/
class DLB_Trie
{
    Node head;  // The head node
    int size;   // Size of the trie

    public DLB_Trie()
    {
        head = new Node();
        size = 0;
    }

    /*
        Method put: places a new word into our trie
            String word: the word we're placing into the trie
    */
    public void put(String word)
    {
        // If our word doesn't have anything in it, return a null
        if(word.equals(null) || word.equals(""))
        {
            System.out.println("The String is blank or null");
        }
        else
        {
            // The below is just debug code
            //System.out.println("Putting word " + word);
            word = word + "^";  // Want to add on our end character to signify the end of the word
            put(head, word, 0);
        }
    }

    /*
        Method put: places the new word into the trie step-by-step with recursion
            Node node: the node we're checking/modifying
            String word: the word we're adding
            int depth: how far we are into the trie
    */
    public void put(Node node, String word, int depth)
    {
        // If the node value is null place the next value into it
        if(node.val == '\u0000')
        {
            node.val = word.charAt(depth);  // Set the value

            // If we've used up the entire word, we need to go back now.
            if(depth == word.length()-1)
            {
                // Since this is the end of the word, we can store the full word here in the carrot node
                node.value = word.substring(0, word.length()-1);
                // Change our total trie size if we need to
                if(depth > size)
                {
                    size = depth;
                    // End this recursion process
                }
            }
            else
            {
                if(node.child == null) node.child = new Node(); // If the child node pointer is null, make a new node
                put(node.child, word, depth+1); // Go on to the child node
            }
        }
        // If the node contains the same value as our word, go onto its child.
        else if(node.val == word.charAt(depth))
        {
            if(node.child == null) node.child = new Node(); // If the child node pointer is null, make a new node.
            put(node.child, word, depth+1); // Go onto the child node.
        }
        // If the node doesn't have the value we need, go onto the next sibling.
        else if(node.val != word.charAt(depth))
        {
            if(node.next == null) node.next = new Node(); // If the sibling node pointer is null, make a new node.
            put(node.next, word, depth);
        }
    }

    /*
        Method put: places a new word into our trie. This put method is used for the history trie, and includes
        a section for inputting the frequency of a word
            String word: the word we're placing into the trie
            int frequency: the frequency that the word has been searched
    */
    public void put(String word, int frequency)
    {
        // If our word doesn't have anything in it, return a null
        if(word.equals(null) || word.equals(""))
        {
            System.out.println("The String is blank or null");
        }
        else
        {
            // The below is just debug code
            //System.out.println("Putting word " + word);
            word = word + "^";  // Want to add on our end character to signify the end of the word
            put(head, word, 0, frequency);
        }
    }

    /*
        Method put: places the new word into the trie step-by-step with recursion
            Node node: the node we're checking/modifying
            String word: the word we're adding
            int depth: how far we are into the trie
            int frequency: the frequency that the word has been searched
    */
    public void put(Node node, String word, int depth, int frequency)
    {
        // If the node value is null place the next value into it
        if(node.val == '\u0000')
        {
            node.val = word.charAt(depth);  // Set the value

            // If we've used up the entire word, we need to go back now.
            if(depth == word.length()-1)
            {
                // Since this is the end of the word, we can store the full word here in the carrot node
                node.value = word.substring(0, word.length()-1);
                // Since this is our frequency-put, we can include our frequency number as well
                node.freq = frequency;
                // Change our total trie size if we need to
                if(depth > size)
                {
                    size = depth;
                    // End this recursion process
                }
            }
            else
            {
                if(node.child == null) node.child = new Node(); // If the child node pointer is null, make a new node
                put(node.child, word, depth+1, frequency); // Go on to the child node
            }
        }
        // If the node contains the same value as our word, go onto its child.
        else if(node.val == word.charAt(depth))
        {
            if(node.child == null) node.child = new Node(); // If the child node pointer is null, make a new node.
            put(node.child, word, depth+1, frequency); // Go onto the child node.
        }
        // If the node doesn't have the value we need, go onto the next sibling.
        else if(node.val != word.charAt(depth))
        {
            if(node.next == null) node.next = new Node(); // If the sibling node pointer is null, make a new node.
            put(node.next, word, depth, frequency);
        }
    }

    /*
        Method get: returns a Node from the trie
            String key: The String to find the Node required
    */
    public Node get(String key)
    {
        // If the head is null, there's nothing in the trie
        if(head.val == '\u0000') return null;
        // If the string is null (somehow) we need to return as well
        if(key.equals(null) || key.equals("")) return null;
        // Otherwise, we use the prefix in order to find the intended node, and pass it back
        Node gotNode = get(head, key, 0);
        return gotNode;
    }

    public Node get(String key, boolean bool)
    {
        // If the head is null, there's nothing in the trie
        if(head.val == '\u0000') return null;
        // If the string is null (somehow) we need to return as well
        if(key.equals(null) || key.equals("")) return null;
        // Add our carrot value to the key, since we're looking for a full word
        key = key + "^";
        // Otherwise, we use the key in order to find the intended node, and pass it back
        Node gotNode = get(head, key, 0);
        return gotNode;
    }

    /*
        Method get: returns Node from the trie
            Node node: the node it's searching
            String key: the prefix it's looking for
            int depth: how far we are into the trie
    */
    public Node get(Node node, String key, int depth)
    {
        //If we've reached the end of our key...
        if(depth == key.length()-1)  // We cannot go any deeper than our key length
        {
            // If the key at this point matches our character, return the node
            if(node.val == key.charAt(depth)) return node;
            // If it doesn't match our character, and the sibling isn't null, move to the sibling
            if(node.next != null) return get(node.next, key, depth);
            // Otherwise we've finished looking
            return null;
        }
        // If we've not at the end of our key...
        // If the character matches, and a child exists, go there.
        if(node.val == key.charAt(depth) && node.child != null) return get(node.child, key, depth+1);
        // If the character doesn't match, and a sibling exists, go there.
        if(node.val != key.charAt(depth) && node.next != null) return get(node.next, key, depth);
        // Otherwise, we've finished looking
        return null;
    }

    /*
        Method getWords: fills up a queue full of suggested words for the user
            LinkedList<Node> aQueue: Our queue reference that we're gonna fill up
            Node node: the end of our prefix, and where we're going to start.
            int depth: how far we are into our subtrie. It will start at 0.
    */
    public void getWords(LinkedList<Node> aQueue, Node node, int depth)
    {
        if(aQueue.size() < 5)
        {
            // We're on a carrot hunt! If there's a carrot there, we want the word inside.
            if(node.val == '^'){
                // But we only want that value if it doesn't already exist in the list
                if(!inTheList(aQueue, node.value))  // This contains is part of the LinkedList, that's why it's not working
                {
                    aQueue.add(node);
                }
            }
            // If that node has a child, we want to go there next.
            if(node.child != null) getWords(aQueue, node.child, depth+1);
            // This means we went over our depth, and our 'root' node cancelled us out
            if(depth == 0) return;
            // If we've come all the way back up from the children, we want to try the sibling next
            if(node.next != null) getWords(aQueue, node.next, depth);
        }
    }

    /*
        Method inTheList: a small method used to test whether a given word exists within a linked list of nodes
    */
    public boolean inTheList(LinkedList<Node> aList, String word)
    {
        // Test each value of the list against the word, and return true if it is found
        for(int k = 0; k < aList.size(); k++)
        {
            if(word.equals(aList.get(k).value))
            {
                return true;
            }
        }

        return false;
    }

    public void getRadixWords(LinkedList<Node> aQueue, Node node, int depth)
    {
        // We're on a carrot hunt! If there's a carrot there, we want the word inside.
        if(node.val == '^'){
            // But we only want that value if it doesn't already exist in the list
            if(!inTheList(aQueue, node.value))  // This contains is part of the LinkedList, that's why it's not working
            {
                aQueue.add(node);
            }
        }
        // If that node has a child, we want to go there next.
        if(node.child != null) getWords(aQueue, node.child, depth+1);
        // This means we went over our depth, and our 'root' node cancelled us out
        if(depth == 0) return;
        // If we've come all the way back up from the children, we want to try the sibling next
        if(node.next != null) getWords(aQueue, node.next, depth);
    }

    public void getAllWords(LinkedList<Node> aList)
    {
        if(this.head.val == '\u0000') return;   // If the head is null, return
        if(aList == null) return;               // If the list is null, return
        getAllWords(aList, this.head);          // Otherwise, get all the words in the trie
    }
    /*
        Method getAllWords: A method to get all of the words that exist within the trie
            LinkedList<Node> aList: Used to store all of the trie words retreived
            Node node: our starter node
    */
    public void getAllWords(LinkedList<Node> aList, Node node)
    {
        // Another carrot hunt! Find all of the carrots in the trie and write them into the list provided
        if(node.val == '^')
        {
            aList.add(node);
        }
        // If that node has a child, we want to go there next
        if(node.child != null) getAllWords(aList, node.child);
        // If we've come back up from the children, we want to try the sibling next
        if(node.next != null) getAllWords(aList, node.next);
    }

    /*
        Method isEmpty: returns true if the trie is empty, false if something is in it.
    */
    public boolean isEmpty()
    {
        if(this.head.val == '\u0000') return true;
        return false;
    }

    /*
        Method contains: Tests whether the given string is in the trie
            Node node: the node with the word we're testing for
    */
    public boolean contains(Node node)
    {
        String contained = "";
        // If the key doesn't exist, return false, otherwise begin searching for the key
        if(node == null)
        {
            return false;
        }
        else
        {
            String key = node.value;
            key = key + "^";
            contained = contains(head, key, 0);
        }

        // Determine return value for contains
        if(contained.equals("found"))
        {
            return true;
        }
        else if(contained.equals("oops"))
        {
            System.out.println("There was an oops");
            return false;
        }
        else
        {
            return false;
        }
    }

    /*
        Method contains: For those using contains just for strings
    */
    public boolean contains(String key)
    {
        String contained = "";
        // If the key doesn't exist, return false, otherwise begin searching for the key
        if(key == null || key == "")
        {
            return false;
        }
        else
        {
            key = key + "^";
            contained = contains(head, key, 0);
        }

        // Determine return value for contains
        if(contained.equals("found"))
        {
            return true;
        }
        else if(contained.equals("oops"))
        {
            System.out.println("There was an oops");
            return false;
        }
        else
        {
            return false;
        }
    }

    public String contains(Node node, String key, int depth)
    {
        String response;
        // If we find the last character in our string, we need to return that we found it
        if(node.val == key.charAt(depth))
        {
            if(key.charAt(depth) == '^')
            {
                response = "found";
                return response;
            }
            else    // Move on to the next child node
            {
                if(node.child != null)  // There is a child node in there
                {
                    response = contains(node.child, key, depth+1);
                }
                else    // We didn't find it
                {
                    response = "not found";
                }
            }
        }
        // If the letter isn't the right one, move on to the sibling
        else if(node.val != key.charAt(depth))
        {
            if(node.next != null)  // Make sure the sibling isn't null
            {
                response = contains(node.next, key, depth);
            }
            else    // There are no more siblings
            {
                response = "not found";
            }
        }
        else 
        {
            // Something went wrong, this is not good
            response = "oops";
            return response;
        }
        return response;
    }
}

// Node class for node functions
class Node
{
    char val;       // The value of the node
    String value;   // The word being held in the Node (only if a carrot)
    int freq;       // The frequency of the word's usage
    Node next;      // The node's sibling
    Node child;     // The node's child

    public Node()   // Create a new, null node
    {
        val = '\u0000'; // Just to make sure it's null -_ -
        freq = 0;
        value = null;
        next = null;
        child = null;
    }
}